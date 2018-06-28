### Installation using VVV

- Download latest stable version of [VVV repository](https://github.com/varying-vagrant-vagrants/vvv) as a .zip
- Follow [installation guide](https://github.com/Varying-Vagrant-Vagrants/VVV#installation---the-first-vagrant-up)
  - VVV Installation Step 4: Please follow this step also
  - VVV Installation Step 5: You already have zip file; Extract zip file to your local directory (let's call this dir `[PATH_TO_VVV]`)
- After `vagrant up` has finished successfuly (for the vary first time), clone this repository to WordPress plugins folder. E.g.
```
cd [PATH_TO_VVV]\www\wordpress-default\wp-content\plugins\
git clone git@github.com:printdotio/gooten-woocommerce.git

```

### Setting up WordPress

- Install, activate and setup WooCommerce plugin in WordPress
- Activate and setup Gooten plugin (We assume that Gooten plugin was already installed)

### Setting up for Unit Tests

- SSH to VM
```
vagrant up
vagrant ssh
```

- Install Composer
```
sudo apt-get install -y php5-cli curl > /dev/null
curl -Ss https://getcomposer.org/installer | php > /dev/null
sudo mv composer.phar /usr/bin/composer
```

- Install PHPUnit
```
composer global require "phpunit/phpunit=4.7.*"
```

- Install PHPUnit includes
```
cd /srv/www/wordpress-default
sudo svn co http://develop.svn.wordpress.org/trunk/tests/phpunit/includes
```

- Run PHPUnit tests (runs all tests...)
```
cd /srv/www/wordpress-default/wp-content/plugins/gooten/tests/phpunit
phpunit tests/
```

### Setting up test enviroment on DigitalOcean

- Go to `/devops/digitalocean/` dir
- Edit `vars.yml` with your valid tokens for GitHub and Digital ocean (mandatory)
- To set up environment execute `ansible-playbook create.yml`
  - Specify name of droplet, use `ansible-playbook create.yml -e "droplet_name=DROPLETNAME"`
- To destroy environment execute `ansible-playbook destroy.yml`
  - When prompted enter name of droplet specified above; e.g. `DROPLETNAME`
