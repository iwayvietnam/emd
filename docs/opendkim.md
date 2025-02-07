OpenDKIM Installation & Configuration
=====================================
### Introduction

Setting up OpenDKIM is essential for creating a trusted email server that ensures your emails won’t be marked as spam. In this tutorial, we’ll walk you through the steps of installing and configuring OpenDKIM

### Enable the necessary repositories
* RHEL Compatibility OS (CentOS, Alma Linux, Rocky Linux, ...)
First, you’ll need to enable the EPEL repository by running:
```sh
dnf -y install epel-release
```

Then you’ll have to enable the CodeReady builder repository:
```sh
dnf config-manager –set-enabled crb
```

### Install OpenDKIM and the related tools
```sh
dnf -y install opendkim opendkim-tools opendbx-mysql
```

### Configure OpenDKIM
OpenDKIM configuration file is located in /etc/opendkim.conf, so before making any changes make a backup:
```sh
cp /etc/opendkim.conf{,.orig}
```

Type the following command to edit the OpenDKIM configuration file:
```sh
vim /etc/opendkim.conf
```

Add/edit the following information:
```ini
AutoRestartRate         10/1h
LogWhy                  Yes
Syslog                  Yes
SyslogSuccess           Yes
Mode                    sv
Canonicalization        relaxed/simple
SigningTable dsn:mysql://emd:emdPassw0rd@emd-mariadb/emd/table=dkim_keys?keycol=domain?datacol=id
KeyTable     dsn:mysql://emd:emdPassw0rd@emd-mariadb/emd/table=dkim_keys?keycol=id?datacol=domain,selector,private_key
SignatureAlgorithm      rsa-sha256
Socket                  inet:8891@localhost
PidFile                 /var/run/opendkim/opendkim.pid
UMask                   022
UserID                  opendkim:opendkim
TemporaryDirectory      /var/tmp
```
Replace `SigningTable` & `KeyTable` with your database configuration.

### Enable and start OpenDKIM service
```sh
systemctl enable opendkim
systemctl start opendkim
```

### Integrate OpenDKIM with Postfix
To integrate OpenDKIM with Postfix, we need to add the following lines to /etc/postfix/main.cf:
```ini
smtpd_milters           = inet:127.0.0.1:8891
non_smtpd_milters       = $smtpd_milters
milter_default_action   = accept
milter_protocol         = 2
```

Restart Postfix service
```sh
systemctl restart postfix
```
