# Contributing

We try to keep master as a reflection of production and all work is done in the develop branch.
To facilitate this work you can install `git flow`, a series of commands on top of git
that allow feature branching and versioning release

Make your change. Write tests. Follow the [WordPres style guide](https://codex.wordpress.org/WordPress_Coding_Standards).

## Install git-flow

Check [git flow page](https://github.com/nvie/gitflow/wiki/Installation) for instructions

## Initialize git-flow

```sh
$ git flow init
```

### Features:

Every new change should be done in a feature branch, 
these are the instructions for working with features and git flow

  + Start a new feature from the 'develop' branch:
  
    ```sh
    $ git flow feature start <your feature>
    ```
    
    This action creates a new feature branch based on 'develop' and switches to it.

  + Publish a feature:
  
    ```sh
    $ git flow feature publish <your feature>
    ```
    
    Publish a feature to the remote server so it can be used or reviewed by other users.

  + Getting a published feature:
    
    ```sh
    $ git flow feature pull origin <your feature>
    ```

    Get a feature published by another user.

  + Finish a feature:
    
    ```sh
    $ git flow feature finish <your feature>
    ```

    - Merged <your feature> into 'develop'.
    - Removes the feature branch.
    - Switches back to 'develop' branch.

## More

On using git flow: 
* http://nvie.com/posts/a-successful-git-branching-model/
* https://danielkummer.github.io/git-flow-cheatsheet/