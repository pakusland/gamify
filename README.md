# gamify - The core of Game of Work!

## Presentation

gamify is the core of [Game of Work](https://gow.upcnet.es) application. It's a gamification platform created by **Emilio Ampudia** (eampudia _at_ gmail.com) and **Paco Orozco** (paco _at_ pacoorozco.info). 

This project was bornt while we were creating **Game of Work**, _aka GoW!_, a gamification platform in [UPCnet](http://www.upcnet.es). We wanted to teach, learn and share some fun with our colleagues, so we created a game work based on questions about our organization (process, services, teams...).

It's a PHP 5 application and you will need MySQL as a database.

As much as possible, we have tried to keep a clean code to work everywhere out of the box. You are not obliged to use our tools and are free to change the code in order to use it at your own feeling.

## Changelog

See [CHANGELOG](https://github.com/pacoorozco/gamify/blob/master/CHANGELOG) file in order to know what changes are implemented in every version.

## Installation

### Clone the Source

Clone GitLab repository

```$ git clone https://github.com/pacoorozco/gamify.git gamify```

### Configuration
* Copy [gamify.conf.sample](https://github.com/pacoorozco/gamify/blob/master/gamify.conf.sample) to **gamify.conf**. 
* Edit it and put valid values

Then enjoy !

## Update

Go to the dir where application is installed ($APP_DIR) and use git to pull new code.

```$ git https://github.com/pacoorozco/gamify.git gamify```

Please see [install/](https://github.com/pacoorozco/gamify/tree/master/install) dir in order to see if DB schema must be upgraded.

## Reporting issues

If you have issues with **gamify**, you can report them with the [GitHub issues module](https://github.com/pacoorozco/gamify/issues).

Please remember to provide as much information as you can.

## License

**gamify** is released as free software under [GPLv2](http://www.gnu.org/licenses/gpl-2.0.html)

## Authors

* Emilio Ampudia (eampudia _at_ gmail.com)
* [Paco Orozco](http://pacoorozco.info) (paco _at_ pacoorozco.info)
