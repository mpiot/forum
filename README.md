### Summary
1. Install the development app
2. Follow the best practice
3. How to control your code syntax ?

## 1. Install the development app

### 1. Install Docker and docker-compose
The development app use docker and docker-compose, before continue to follow the guide, please install these requirements.
* https://docs.docker.com/install/
* https://docs.docker.com/compose/install/

### 2. Configure the app
Now, we will configure the application on your machine, edit:
 - docker-compose.override.ym: configure daemon access like the forwarded ports of nginx to access your app, and db ports
 for debug.

    cp docker-compose.override.yml.dist docker-compose.override.yml
    vi docker-compose.override.yml

### 3. Install
That's finish in a few time, now, just execute:

    make install
    
And voilà !!! Your app is installed and ready to use.

## 2. Follow the best practice
There is a **beautiful** guide about the best practice :) You can find it on the [Symfony Documentation - Best Practice](http://symfony.com/doc/current/best_practices/index.html).

## 3. How to control your code syntax ?
For a better structure of the code, we use Coding standards: PSR-0, PSR-1, PSR-2 and PSR-4.
You can found some informations on [the synfony documentation page](http://symfony.com/doc/current/contributing/code/standards.html).

In the project you have a php-cs-fixer.phar file, [the program's documentation](http://cs.sensiolabs.org/).

Some commands:
   * List files with mistakes

    make php-cs

   * Fix files:

    make php-cs-fix
