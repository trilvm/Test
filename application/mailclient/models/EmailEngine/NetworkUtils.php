<?php
class NetworkUtils {
	static public $g_icmp_error = "No Error";

// timeout in ms
	static function ping($host, $timeout)
	{
        $port = 0;
        $datasize = 64;
        global $g_icmp_error;
        $g_icmp_error = "No Error";
        $ident = array(ord('J'), ord('C'));
        $seq   = array(rand(0, 255), rand(0, 255));

     $packet = '';
     $packet .= chr(8); // type = 8 : request
     $packet .= chr(0); // code = 0

     $packet .= chr(0); // checksum init
     $packet .= chr(0); // checksum init

        $packet .= chr($ident[0]); // identifier
        $packet .= chr($ident[1]); // identifier

        $packet .= chr($seq[0]); // seq
        $packet .= chr($seq[1]); // seq

        for ($i = 0; $i < $datasize; $i++)
                $packet .= chr(0);

        $chk = NetworkUtils::icmpChecksum($packet);

        $packet[2] = $chk[0]; // checksum init
        $packet[3] = $chk[1]; // checksum init

        $sock = socket_create(AF_INET, SOCK_RAW,  getprotobyname('icmp'));
        $time_start = microtime();
    	
        try{
        socket_sendto($sock, $packet, strlen($packet), 0, $host, $port);
        
        }catch(Exception $ex){
			return -1;
		}

    	$read   = array($sock);
        $write  = NULL;
        $except = NULL;

        $select = socket_select($read, $write, $except, 0, $timeout * 1000);
        if ($select === NULL)
        {
                $g_icmp_error = "Select Error";
                socket_close($sock);
                return -1;
        }
        elseif ($select === 0)
        {
                $g_icmp_error = "Timeout";
                socket_close($sock);
                return -1;
        }

    $recv = '';
    $time_stop = microtime();
    socket_recvfrom($sock, $recv, 65535, 0, $host, $port);
        $recv = unpack('C*', $recv);
       
        if ($recv[10] !== 1) // ICMP proto = 1
        {
                $g_icmp_error = "Not ICMP packet";
                socket_close($sock);
                return -1;
        }

        if ($recv[21] !== 0) // ICMP response = 0
        {
                $g_icmp_error = "Not ICMP response";
                socket_close($sock);
                return -1;
        }

        if ($ident[0] !== $recv[25] || $ident[1] !== $recv[26])
        {
                $g_icmp_error = "Bad identification number";
                socket_close($sock);
                return -1;
        }
       
        if ($seq[0] !== $recv[27] || $seq[1] !== $recv[28])
        {
                $g_icmp_error = "Bad sequence number";
                socket_close($sock);
                return -1;
        }

        $ms = ($time_stop - $time_start) * 1000;
       
        if ($ms < 0)
        {
                $g_icmp_error = "Response too long";
                $ms = -1;
        }

        socket_close($sock);

        return $ms;
}

static function icmpChecksum($data)
{
        $bit = unpack('n*', $data);
        $sum = array_sum($bit);

        if (strlen($data) % 2) {
                $temp = unpack('C*', $data[strlen($data) - 1]);
                $sum += $temp[1];
        }

        $sum = ($sum >> 16) + ($sum & 0xffff);
        $sum += ($sum >> 16);

        return pack('n*', ~$sum);
}

static function getLastIcmpError()
{
        global $g_icmp_error;
        return $g_icmp_error;
}

}
?>