## Arlo availability condition ##

A very basic availability condition plugin that allows teachers to restrict access to course activity modules to learners based on their Arlo Order payment status.

The idea is that people can sign up for courses without having paid, go through the course material and not be able to access activities until their Order is marked
as paid in Arlo.

The specific use case this plugin was developed for was to prevent access to certificates if an Order wasn't paid for, however it can be used with any activity module.

## Pre-requisites ##

This plugin requires the [Arlo for Moodle ecommerce | Payments, Shopping cart, CRM, Registration & More](https://moodle.org/plugins/enrol_arlo) plugin to be installed. This plugin
must have the [Order](https://developer.arlo.co/doc/api/2012-02-01/auth/resources/orders) and [OrderLines](https://developer.arlo.co/doc/api/2012-02-01/auth/resources/orderlines)
resource objects defined in the Arlo enrolment plugin in `/c`.

It also requires a current [Arlo](https://www.arlo.co/contact) subscription.

## Installing via uploaded ZIP file ##

1. Log in to your Moodle site as an admin and go to _Site administration >
   Plugins > Install plugins_.
2. Upload the ZIP file with the plugin code. You should only be prompted to add
   extra details if your plugin type is not automatically detected.
3. Check the plugin validation report and finish the installation.

## Installing like a boss ##

The plugin can be also installed by putting the contents of this directory to

    {your/moodle/dirroot}/availability/condition/arlo

Afterwards, log in to your Moodle site as an admin and go to _Site administration >
Notifications_ to complete the installation.

Alternatively, you can run (if you are a CLI warrior)

    $ php {your/moodle/dirroot}/admin/cli/upgrade.php

to complete the installation from the command line.

## What the Moodle versions? ##

So far, this is only written and tested for Moodle 3.9.

## Issues and pull requests ##

If you want some stuff fixed and can write some cool code that tries to align with the Moodle coding guidelines, feel free to write some stuff and submit a PR.
Issues can be raised in GitHub and if you can write some stuff to fix it, feel free to do so and submit a PR :)

## Quick wins ##

If there are any quick wins, let us know :)

## Todo list ##

Need to implement some testing and then add that to some GitHub actions.
Any quick wins, [karawhiua](https://maoridictionary.co.nz/search?idiom=&phrase=&proverb=&loan=&histLoanWords=&keywords=karawhiua) .

## License ##

2022 Skills Consulting Group.

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation,
either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program. If not, see https://www.gnu.org/licenses/.