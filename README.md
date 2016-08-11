Elasticorn - PHP based elasticsearch manager
=============================================

Elasticorn is an elasticsearch manager based on elastica. It's main feature is currently zero-down-time remapping of data.


Features
--------

+ Zero-down time remapping of an index (including data-transfer)
+ Initialize indices based on config files
+ Show and compare mapping configurations


Installation
------------

Composer based:
~~~
composer require T3G/elasticorn
~~~

Download as .phar:

* Insert download link here

Configuration
-------------

For elasticorn to work, your configuration needs to be structured in the following way and be defined as yaml.

- Configuration Directory
  - IndexName directory
    - IndexConfiguration.yaml
    - DocumentTypes directory
      - documenttype.yaml (for example: tweets.yaml)

The IndexConfiguration.yaml file specifies configuration parameters for the index (for example shards or replicas.)
The documenttype.yaml file specifies the mapping configuration for this documenttype.

For an example on how the configuration should look like, see the Tests/Fixtures/Configuration folder in this project.
For a list of available configuration options see the elastica documentation.

You can use a .env file, a command line parameter or the interactive console to specify your configuration directory.

Usage
--------------

composer based usage command:

~~~
./cli.php -h
~~~

phar usage command:

~~~
./elasticorn.phar -h
~~~

Commands
-------------------

+ index:init - Initializes all configured indices
+ index:remap - applies a new mapping configuration to an existing index
+ mapping:compare - allows comparison of currently applied and configured mapping
+ mapping:show - shows currently applied mapping


Zero downtime remapping - How it works:
---------------------------------------

Elasticorn uses aliases to allow zero downtime remapping of an index. For each configured index elasticorn creates an 
index_a and index_b and uses an alias to point to the current active index. When applying a new mapping the 
currently inactive index is deleted and recreated with the new mapping. The data is then copied from the old to the new
index. After the data transfer successfully completes the alias is pointed to the new index. 