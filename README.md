# RobocodeCup
A project for hosting Robocode battles. It contains a website, (a Flight based) api and clientside scripts to run battles. 

The project supports multiple teams, pool of teams (e.g. for grouping teams) and certain rounds in a battle

## Installation
- Clone the repo
- Run ```git submodule init``` and ```git submodule update``` to initialize and update the used submodules

## Subprojects
The project is divided in subprojects. Every project has it's own purpose.

### common
Common classes used in all (or most) of the other modules. This folder should be available on all systems that you use.
These project contains the configuration file (There is a sample provider, please copy it to ```config.inc.php``` and change it to your needs)

### api
The webbased API written in PHP. It contains scripts to fill and update the database and it contains a webroot
that can be accessed in a RESTful way to get data from the database

### client
Scripts to run on a client that supports PHP and Robocode. All scripts are written in PHP. They can be used to parse a large set of downloaded team JAR files
(for example from Blackboard) and extract pool and teamnames from them. After that you can generate and run battles between all of these teams.

### website
The website that shows battle results, built with AngularJS.


