Feature: Truncate a type
  In order to clean up an index
  users should be able to
  remove all documents of a type

  Scenario: Clear all documents of type
    Given I initialized my indices
    And I add some documents of type "tweets"
    When I call elasticorn "type:truncate footest tweets"
    Then There should be no documents of type "tweets"