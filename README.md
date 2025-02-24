# essa
Event Sourcing Scaffold Assembly

## Disclaimer
This library is on **early stage of development**. \
There are many things to do, and many things to improve. \
Critical changes may occur, especially changes that breaks backward compatibility.
Please be aware of that when upgrading library versions.

## Introduction
ESSA is simple project which aims to provide a scaffold for building
DDD & event-sourced applications.

Programmatically, it is a set of interfaces and implementations that
helps you to build a domain model much faster. 

Whole project is designed to be maximally extendable and customizable for
your needs. To achieve this in your project, please refer to usage manuals 
which describes how to use ESSA properly.


## Installation

Using composer
```bash
composer require pfaffkam/essa
```

You can follow next steps which is described in [get started manual](./doc/10-start.md).


## Current dependencies
To make this scaffold useful, there are some concrete implementations,
which based on existing libraries.

Example - for `Identity` interface there is `Id` implementation,
which is based on `symfony/uuid` library.


## Related repositories
Due to packagist mechanics, all ESSA extensions are stored in separate repositories. To help you to navigate over this ecosystem, here is a list of related repositories:

| Repository                                                                                              | Package                                  | Type              | Description                            | 
| --------------------------------------------------------------------------------------------------------| ---------------------------------------- |-------------------|----------------------------------------|
| [pfaffkam/essa-storage-doctrine-connector](https://github.com/pfaffkam/essa-storage-doctrine-connector) | pfaffkit/essa-storage-doctrine-connector | Storage connector | Utilises Doctrine ORM to store events. |
|
|
