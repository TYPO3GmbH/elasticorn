Feature: Cornify an index
  In order to switch to elasticorn a
  user should have the possibility to
  convert an existing index to an elasticorn index

  Scenario: Cornify existing index
    Given I have a non-elasticorn index "mysuperindex"
    When I call elasticorn "index:cornify mysuperindex"
    Then I should see message containing '[info] Creating index mysuperindex'
    And I should have indices starting with "mysuperindex" and a corresponding alias
    And I should have a folder with the new "mysuperindex" configuration

  Scenario: Cornify an existing index with mapping
    Given I have a non-elasticorn index "mysuperindex"
    And I have setup mappings and data for index "mysuperindex"
    When I call elasticorn "index:cornify mysuperindex"
    Then I should have a mapping file for "mysuperindex"
