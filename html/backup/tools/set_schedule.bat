@ECHO OFF
schtasks /delete /tn "backup_qlvbdh" /f
IF %3 == "daily"  ( schtasks /create /tn "backup_qlvbdh" /tr %1 /sc %2  /st %4 /ru system)  ELSE ( schtasks /create /tn "backup_qlvbdh" /tr %1 /sc %2 /d %3 /st %4 /ru system )
