#How to up a Vagrant server to test Pharinix

Requirements:

* Vagrant: https://www.vagrantup.com/ 
* Git: https://git-scm.com/
* You must has port 80 unused, be careful with Skype or similiar software that use port 80. (If port 80 is used change it in Vagrantfile, for example to 8080)

Steps:

* Make a new folder for Pharinix, e. `C:/Projects/` in Windows or `~/Projects/` in Linux
* In that folder run `$ git clone https://github.com/PSF1/pharinix.git` , Pharinix will be cloned in `C:/Projects/pharinix` in Windows or `~/Projects/pharinix` in Linux
* Copy `Vagrantfile`, and `000-default.conf`, to a new folder, e. `C:\vagrant\` in Windows or `~/vagrant/` in Linux
* Edit file Vagrantfile:
 * Change line `config.vm.network "private_network", ip: "192.168.1.11"` to `config.vm.network "private_network", ip: "<a local IP unused>"`
 * Change path `C:/Projects/pharinix` in line `config.vm.synced_folder "C:/Projects/pharinix", "/var/www/html" ` 
 * Change memory in line `vb.memory = "1024"` from 1024 to the amount that you need.
* Next, in that folder, call vagrant up
* Open your browser and go to `http://<a local IP unused>:<port>`, e. `http://192.168.1.11`

That's all

Nice code ;)