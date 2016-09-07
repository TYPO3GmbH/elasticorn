Feature: Remap an index
  In order to apply new mapping configurations
  users should be able to
  remap an index

  Scenario: Remap an existing index (changes existing)
    Given I initialized my indices
    And I use alternative configuration folder with changes
    When I call elasticorn "index:remap footest"
    Then I should see message containing '[info] Remapping footest'

  Scenario: Remap an existing index (no changes)
    Given I initialized my indices
    When I call elasticorn "index:remap footest"
    Then I should see message containing '[info] Remapping footest'