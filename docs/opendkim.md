OpenDKIM Installation & Configuration
=====================================
* Introduction

Setting up OpenDKIM is essential for creating a trusted email server that ensures your emails won’t be marked as spam. In this tutorial, we’ll walk you through the steps of installing and configuring OpenDKIM

* Enable the necessary repositories

First, you’ll need to enable the EPEL repository by running:
```sh
dnf install epel-release
```

Then you’ll have to enable the CodeReady builder repository:
```sh
dnf config-manager –set-enabled crb
```

* Install OpenDKIM and the related tools
```sh
dnf install opendkim opendkim-tools opendbx-mysql
```

* Configure OpenDKIM


* Integrate OpenDKIM with Postfix
