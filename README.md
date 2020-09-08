![Eco logo](./logo.svg)

Eco allows you and your team to effortlessly and securely share non-production environment variables, without the overhead of setting up dedicated secrets servers.

### What's this for?

Have you ever...
- Had a local .env file get deleted or corrupted, causing you to lose environment variables?
- Worked on a team with disorganized or superfluous environment variables?
- Wanted an easy way to securely share environment variables with other project maintainers without need to set up 3rd party secrets management?
- Wanted your team to be able to easily pull an up-to-date copy of project config?

If you answered "yes" to any of these then Eco is for you!

**Important:** Eco is _not_ a secure mechanism for storing and sharing **production-level** environment variables. It's not. Please don't.

## How it works

Eco is actually pretty simple. It operates using 3 different storage mechanisms:

1. **Your project `.env` file.** This is where the values you're actually using live, because your project depends on them.
2. **Your local "vault".** The vault is a local config file where you can permanently store any environment variable you don't want to lose. This gives you the ability to nuke your `.env` file and then just pull in the keys you want to restore.
3. **The remote `.eco` file.** When you push keys you want shared by the team, Eco creates an `.eco` file in the root of your repo, directly in the `master` branch. Inside the `.eco` file are your shared keys, all encrypted using [the same strategy used by Github when store repository secrets](https://docs.github.com/en/rest/reference/actions#create-or-update-a-repository-secret). This file will store unique key:value pairs that your team pushes to it.

## Documentation

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

Eco comes with a number of commands to manage local and remote environment variables.

#### `eco init`

Should be the first thing you run after installing. As seen above, it requires a Github PAT.

#### `eco repo:list`

List available repositories in your organization.

#### `eco repo:switch`

List available repositories in your organization and allow you switch to a different repo. This allows you to use Eco across different repositories. Just don't forget to switch repos before pushing or pulling!

#### `eco repo:current`

Show the current working repository.

#### `eco env:fresh`

Fetch the `.env.example` file from your project repository and copy it as your new local `.env` file. This is a desctructive command, so you are asked to confirm.

#### `eco env:set`

Create or update a key:value pair in your local vault and will add it to your local `.env` file.

#### `eco env:unset`

Remove a key:value pair from your local vault and will remove it from your local `.env` file.

#### `eco env:push`

Push a key:value pair into the remote `.eco` file.

#### `eco env:pull`

Fetch a key:value pair from the remote `.eco` file and add or update it in your local `.env` file.

## Contributing

If you're interested in contributing to Eco, please read our [contributing guide](https://github.com/hotmeteor/eco-cli/blob/master/.github/CONTRIBUTING.md).
