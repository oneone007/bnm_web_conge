
└─$ lsof -i :5003

COMMAND   PID  USER FD   TYPE DEVICE SIZE/OFF NODE NAME
python  13243 mrone 3u  IPv4 138896      0t0  TCP *:5003 (LISTEN)
                                                                                
┌──(mrone㉿one)-[/opt/lampp/htdocs/bnm_web]
└─$ kill -9 13243

                    
                    