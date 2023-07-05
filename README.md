# WordPress Plugin Membership Pro Tdv 
* Manage your TradingView subscriptions effortlessly with this powerful plugin for WordPress. This plugin automates the subscription process and ensures a secure and reliable connection with TradingView. You can focus on your trading strategies while this plugin takes care of the rest.
* This plugin is an extend of Membership Pro Pro to add/remove automatically username of TradingView.

**If you like this plugin please support me a coffee via paypal clickclone@gmail.com or need implement a plugin contact donald.nguyen.it@gmail.com**

## Wordpress Coding Standards - Check and Fix issues before deploy on wordpress.org

**Install package to check Wordpress standard:**

```sh
composer install
```

**Get all errors of the project:**

```sh
vendor/bin/phpcs --standard=WordPress .
```

**Fix all errors of the project:**

```sh
vendor/bin/phpcbf --standard=WordPress .
```

or fix manually

## Features
**Add username to TradingView.**
**Remove username from TradingView in cases:**
* The order was refund, failed or cancel
* A subscriber was deleted.


## Installation

* Step 1: Get Membership Pro Pro from https://codecanyon.net/item/ultimate-membership-pro-wordpress-plugin/12159253. Install and active both Membership Pro Pro and this plugin.

* Step 2: Login TradingView and get sessionid and get token from console.

![Alt text](https://github.com/dearvn/membership-pro-tdv/raw/main/sessionid.png?raw=true "Token")


 then input it in setting and save.

![Alt text](https://github.com/dearvn/membership-pro-tdv/raw/main/setting_token.png?raw=true "Setting")


* Step 3: Add a field with name `tradingview_username` in register page follow this guide: https://help.wpindeed.com/ultimate-membership-pro/knowledge-base/add-custom-fields/


![Alt text](https://github.com/dearvn/membership-pro-tdv/raw/main/custom_field.png?raw=true "Custom field")

* Step 4: Edit a plan and choose Chart Id to assign

![Alt text](https://github.com/dearvn/membership-pro-tdv/raw/main/plan.png?raw=true "Plan")

* Step 5: In register page, the user will input username of TradingView

![Alt text](https://github.com/dearvn/membership-pro-tdv/raw/main/register.png?raw=true "Register")


## License

The WordPress Plugin Membership Pro Tdv is licensed under the GPL v2 or later.

> This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License, version 2, as published by the Free Software Foundation.

> This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

> You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA

A copy of the license is included in the root of the plugin’s directory. The file is named `LICENSE`.


### Licensing

The WordPress Plugin Membership Pro Tdv is licensed under the GPL v2 or later; however, if you opt to use third-party code that is not compatible with v2, then you may need to switch to using code that is GPL v3 compatible.

For reference, [here's a discussion](http://make.wordpress.org/themes/2013/03/04/licensing-note-apache-and-gpl/) that covers the Apache 2.0 License used by [Bootstrap](http://twitter.github.io/bootstrap/).

### Includes

Note that if you include your own classes, or third-party libraries, there are three locations in which said files may go:

* `includes` is where functionality where you can put all your admin code, custom classes.

## Supports for custom development.

If you’re interested in custom plugin development or website customization please contact us. donald.nguyen.it@gmail.com
