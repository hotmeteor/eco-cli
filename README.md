![Eco logo](./logo.svg)

Eco allows you and your team to effortlessly and securely share non-production environment variables, without the overhead of setting up dedicated secrets servers.

### Installation

Eco CLI may be installed globally or on a per-project basis using Composer:

```shell script
$ composer require hotmeteor/eco-cli
 
$ composer global require hotmeteor/eco-cli
```

### Getting Started

Once Eco is installed it needs to be set up. Ideally, this is done within the folder for the project you are collaborating on. 

A [Github Personal Access Token](https://docs.github.com/en/github/authenticating-to-github/creating-a-personal-access-token) is required. It should be created with the `repo` and `read:org` permissions.

```shell script
$ eco init
```

You will be asked to select the owner or organization to act under, as well as the repository for the current project.

### Usage

Eco comes with a number of commands to manage local, remote, and team environment variables.