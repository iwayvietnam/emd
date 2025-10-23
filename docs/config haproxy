#######
frontend 587
bind *:587
mode tcp
timeout client 1m
log global
option tcplog
maxconn 4096
### create acl
acl auth_otp_ip src -f /etc/haproxy/auth_otp.txt
acl auth_care_ip src -f /etc/haproxy/auth_care.txt
acl auth_relay_ip src -f /etc/haproxy/auth_relay.txt
acl auth_vpb_ip src -f /etc/haproxy/auth_vpb.txt
acl auth_einvoice_ip src -f /etc/haproxy/auth_einvoice.txt
acl relay_ip src -f /etc/haproxy/open_relay.txt
acl internal_ip src -f /etc/haproxy/internal.txt
acl postfix_ad_ip src -f /etc/haproxy/postfix_ad.txt
### assign backend work
use_backend bk_otp if auth_otp_ip
use_backend bk_auth_care if auth_care_ip
use_backend bk_auth_relay if auth_relay_ip
use_backend bk_auth_vpb if auth_vpb_ip
use_backend bk_auth_einvoice if auth_einvoice_ip
use_backend bk_relay if relay_ip
use_backend bk_internal if internal_ip
### 
use_backend bk_no-authenticate if postfix_ad_ip
use_backend bk_no-authenticate if postfix_ad_ip
use_backend bk_authenticate if { src 1.1.1.1 }
###backend no rules
default_backend bk_no_rules


###backend postfix-gateway
backend bk_no-authenticate
mode tcp
log global
#option tcplog 
timeout server 1m
timeout connect 30s
server postfix-gateway 1 send-proxy

###
#backend postfix-ad
backend bk_authenticate
mode tcp
log global
#option tcplog 
timeout server 1m
timeout connect 30s
server postfix-ad 1 send-proxy

###
#backend postfix-otp
backend bk_otp
mode tcp
log global
#option tcplog 
timeout server 1m
timeout connect 30s
server postfix-ad 1 send-proxy

###backend auth_care
backend bk_auth_care
mode tcp
log global
#option tcplog 
balance roundrobin
timeout server 1m
timeout connect 30s
server old-auth-care84 2 check weight 3
server old-auth-care85 2 check weight 3
server old-auth-care88 2check weight 3
server new-auth 1 check send-proxy weight 1

###backend auth_relay
backend bk_auth_relay
mode tcp
log global
#option tcplog 
balance roundrobin
timeout server 1m
timeout connect 30s
server old-auth-relay55 10 check weight 4
server old-auth-relay56 10 check weight 4
server new-auth 10 check send-proxy weight 1

###backend auth_vpb
backend bk_auth_vpb
mode tcp
log global
#option tcplog 
balance roundrobin
timeout server 1m
timeout connect 30s
server old-auth-vpb44 202 check weight 4
server old-auth-vpb45 202 check weight 4
server new-auth 10 check send-proxy weight 1

###backend auth_einvoice
backend bk_auth_einvoice
mode tcp
log global
#option tcplog 
balance roundrobin
timeout server 1m
timeout connect 30s
server old-auth-einvoice54 20 check weight 9
server new-auth 10 check send-proxy weight 1

###backend relay
backend bk_relay
mode tcp
log global
#option tcplog 
balance roundrobin
timeout server 1m
timeout connect 30s
server old-relay200 20 check weight 4
server old-relay223 20 check weight 4
server new-no-auth 10 check send-proxy weight 1

###backend internal
backend bk_internal
mode tcp
log global
#option tcplog 
balance roundrobin
timeout server 1m
timeout connect 30s
#server old-internal53  check weight 9
server new-no-auth 10.16 check send-proxy weight 1

###backend no-rules
backend bk_no_rules
mode tcp
log global
#option tcplog 
timeout server 1m
timeout connect 30s
server no-rules 10. check send-proxy


###
frontend http-in

  bind *:80
  bind *:443 ssl crt /root/ssl/mailproxy.pem
  http-request redirect scheme https unless { ssl_fc }
  option forwardfor
#  redirect scheme https if !{ ssl_fc }
  default_backend backend_http

backend backend_http
  mode http
  http-request set-header X-Forwarded-Proto https if { ssl_fc }
  http-request set-header X-Forwarded-Proto http unless { ssl_fc }
  server http-in 10. check


###
frontend log

  bind *:5601
  default_backend backend_monitor

backend backend_monitor
  server monitor 10.16:5601 check
                                               
