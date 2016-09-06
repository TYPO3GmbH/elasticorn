Feature: Init indices
  In order to start working with elasticsearch
  users should be able to
  create zero-down-time indices with an alias to the current live index

  Scenario: Initialize configured index
    Given I don't have any indices
    When I call elasticorn "index:init"
    Then I should see message containing '[info] Loading configuration from Tests/Fixtures/Configuration/'
    And I should see message containing '[info] Creating index footest'