Elasticorn - PHP based elasticsearch manager
=============================================

Elasticorn is an elasticsearch manager based on elastica. It's main feature is zero-down-time remapping of data.


Features
--------

+ Zero-down time remapping of an index (including data-transfer)
+ Initialize indices based on config files
+ Show and compare mapping configurations

Zero downtime remapping - How it works:
---------------------------------------

Elasticorn uses aliases to allow zero downtime remapping of an index. For each configured index elasticorn creates an 
index_a and index_b and uses an alias to point to the current active index. When applying a new mapping the 
currently inactive index is deleted and recreated with the new mapping. The data is then copied from the old to the new
index. After the data transfer successfully completes the alias is pointed to the new index. 

### Step by Step

+ Your applications index is **not** called like in your config.
+ Your applications index is indeed **two** separate indices and your configured index name is only an **alias** to the current live-index. We refer to the other index as the "standby-index".
+ When changing the mapping of an index, all data of that index has to be deleted because your mapping might influence the way how your data had been analyzed before.
+ Elasticorn will determine if your mapping has changed in between runs
+ Elasticorn will determine which index is your current live-index and switch to your standby index
+ Elasticorn will then apply your new mappings onto the standby index
+ Elasticorn will copy all data from your live-index to the (freshly mapped) standby-index. This will analyze your data again as you would expect.
+ Once finished, Elasticorn will change the alias from the "old" live-index to the "old" standby-index. This way the standby-index becomes the new live-index
+ Elasticorn will delete all data from your "old" live-index to free up resources.
+ Since switching the alias is an atomic action, your users will not experience any downtime whatsoever.
+ Enjoy providing an awesome service to your users.

Installation
------------

Composer based:

~~~
composer require T3G/elasticorn
~~~

Download as .phar:

* Insert download link here

Elasticsearch Client Configuration
----------------------------------

Elasticorn assumes default connection parameters for establishing a connection to elasticsearch. 
If you are using a non-default setup you can configure those connection settings in a .env file. 
For details see below.


Index and Mapping Configuration
-------------------------------

For elasticorn to work, your configuration needs to be structured in the following way and be defined as yaml.

~~~
- MAIN configuration directory
  - IndexName directory
    - IndexConfiguration.yaml
    - DocumentTypes directory
      - documenttype.yaml (for example: tweets.yaml)
~~~

### Example

~~~
project
│   README.md    
└───Elasticorn
	└── t3_forger
    		├── DocumentTypes
    		│   ├── issue.yaml
    		│   ├── review.yaml
    		│   └── user.yaml
    		└── IndexConfiguration.yaml
~~~

In our case the `Elasticorn` holds all information about our indices. Multiple indices can be managed by
creating new folders.

The `IndexConfiguration.yaml` file specifies configuration parameters for the index (for example shards or replicas.)

The folder called `DocumentTypes` holds our type mapping with a file per document type.

The syntax is pretty straightforward yaml syntax which will then be parsed as an array.

The filename will determine the name of your document type in Elasticsearch.

We'll take a look at `user.yaml` here:

~~~
id:
  type: integer
username:
  type: string
  index: not_analyzed
  store: true
fullname:
  type: string
  index: not_analyzed
  store: true
email:
  type: integer
  index: not_analyzed
  store: true
avatar:
  type: string
~~~

For an example on how the configuration should look like, see the Tests/Fixtures/Configuration folder in this project.
For a list of available configuration options see the elastica documentation.

You can use a .env file, a command line parameter or the interactive console to specify your configuration directory.

### .env configuration

You can specify your configuration directory as well as specific connection params in a .env file which should be 
placed in the folder where elasticorn gets executed. The following variables may be configured:

~~~
configurationPath=
elastica.host=
elastica.port=
elastica.path=
elastica.url=
elastica.transport=
elastica.persistent=
elastica.timeout=
elastica.username=
elastica=password=
~~~

Usage
--------------

composer based usage command:

~~~
./elasticorn.php -h
~~~

phar usage command:

~~~
./elasticorn.phar -h
~~~

Commands
-------------------

+ `index:init` - Initializes all configured indices
+ `index:remap` - applies a new mapping configuration to an existing index
+ `index:cornify` - Converts a conventional index to an elasticorn index
+ `mapping:compare` - allows comparison of currently applied and configured mapping
+ `mapping:show` - shows currently applied mapping