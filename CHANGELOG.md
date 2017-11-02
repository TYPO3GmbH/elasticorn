CHANGELOG
=========

Features and Bugfixes per version
---------------------------------

### 5.1.0
+ [FEATURE] Add possibility to truncate documents of specified type
+ [BUGFIX] Fix self-update for future versions

### 5.0.0
+ [FEATURE] Add compatibility to elasticsearch 5

### 1.1.2
+ [BUGFIX] Fix broken .env detection

### 1.1.1
+ [BUGFIX] Fix path issue with composer installations

### 1.1.0
+ [FEATURE] Allow new languages to be added by remap
+ [FEATURE] Add language aware remapping
+ [FEATURE] Add initializing of indices in different languages
+ [FEATURE] Add force option to remap and remap only on changes
+ [FEATURE] Add behat tests for index:cornify and index:remap
+ [FEATURE] Add behat test for index:init
+ [FEATURE] Add first behat tests for mapping commands
+ [FEATURE] Add longer installation instructions
+ [FEATURE] Show command displays in a more readable manner

### 1.0.2
+ [BUGFIX] Lowercase composer package name

### 1.0.1
+ [BUGFIX] Fix paths in phar
+ [BUGFIX] Load .env file from outside of the .phar archive
+ [FEATURE] Add self-update and rollback capabilities
+ [FEATURE] Configurable elastica client settings
+ [FEATURE] Add possibility to "cornify" an existing index
+ [BUGFIX] Adjusted expected results to Fixtures
+ [BUGFIX] Differ diffs stupidly
+ [FEATURE] Add possibility to load config path from .env file
+ [FEATURE] Add compare and show commands


Cleanups
--------
### 1.1.2

+ [TASK] Added Scrutinizr
+ [TASK] Spacing around declare
+ [TASK] Code Cleanup
+ [TASK] Adjust issue url

### 1.1.1
+ [TASK] Update README.md

### 1.1.0
+ [TASK] Update documentation with language feature
+ [TASK] Replace recursion with while
+ [TASK] Cleanup and add new tests
+ [TASK] Fix comparison and index initialization
+ [TASK] Fix tests
+ [TASK] Refactor IndexUtility to IndexService
+ [TASK] Remove deprecations and adjust Tests
+ [TASK] Cleanup IndexUtility
+ [TASK] Add contribution and testing part to readme
+ [TASK] Update dependencies
+ [TASK] Test CI composer validate
+ [TASK] Move GitHub pages from branch gh-pages to docs folder.
+ [TASK] add cornify command to README

### 1.0.2
+ [TASK] Add homepage to composer.json

### 1.0.1
+ [TASK] Re-use correct differ (merge-conflict foo :()
+ [TASK] Update composer.lock
+ [TASK] Adjust readme
+ [TASK] Add update and rollback only in phar version
+ [TASK] Add rollback output
+ [TASK] Prepare versioning and signing
+ [TASK] Change suffix to uniqid
+ [TASK] Remove unused log manager
+ [TASK] Remove faker from composer reqs
+ [TASK] Beautify code
+ [TASK] Use new flatten implementation
+ [TASK] Change Tests to namespaced
+ [TASK] Show only diff if things changed, show only changed lines
+ [TASK] Show only diff if things changed, show only changed lines
+ [TASK] Flatten array before diffing
+ [TASK] Added more documentation to the README.md
+ [TASK] Fix comparison and add unicorn
+ [TASK] adjust bin dir
+ [TASK] Various bug fixes
+ [TASK] Add documentation
+ [TASK] Add Test and reformat
+ [TASK] Add dependency injection and logging
+ [TASK] Cleanup
+ [TASK] Add commands and box config
+ [TASK] Add basic setup
+ [TASK] template with README and .gitignore
+ [TASK] Initialize Repository
