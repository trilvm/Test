<?php
	class Mail_Mime{
		static function decode_mimeheader($encode_data){
			$result="";
			$arr_ex = explode('?',$encode_data);
			if(strtoupper($arr_ex[2]) == "Q"){
				$result .= quoted_printable_decode($arr_ex[3])." ";
			}else if(strtoupper($arr_ex[2]) == "B"){
				$result .= base64_decode($arr_ex[3])." ";
			}else {
				$decode64 = base64_decode($encode_data,$is_base64);
				if($is_base64)
					$result .= $decode64;
				else 
					$result = $encode_data ;
			}
			return $result;
		}
		
		static function parseEmailAddress($stremail){
			$arr_address = array();
			$arr_parse = mailparse_rfc822_parse_addresses($stremail);
			foreach($arr_parse as $it){
				$display = Mail_Mime::decode_mimeheader($it["display"]);
				$address = $it["address"];
				$arr_address[] = "$display <$address>";
			}
			return $arr_address;
		}

		
		static function explodeEmailAddress($stremail){
			$arr_address = array();
			$arr_parse = mailparse_rfc822_parse_addresses($stremail);
			foreach($arr_parse as $it){
				$display = Mail_Mime::decode_mimeheader($it["display"]);
				$address = $it["address"];
				$arr_temp = array();
				$arr_temp["display"] = $display;
				$arr_temp["address"] = $address;
				$arr_address[] = $arr_temp;
			}
			return $arr_address;
		}
		
		static function getAddressDescription($email_address_single){
			
			$arr_parse = mailparse_rfc822_parse_addresses($email_address_single);
			$display = Mail_Mime::decode_mimeheader($arr_parse[0]["display"]);
			$address = $arr_parse[0]["address"];
			if($display != "")
				return $display;
			else 
				return $address;
			
			return $display;
		}
	}
?>