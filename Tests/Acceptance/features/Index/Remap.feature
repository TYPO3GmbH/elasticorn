Feature: Remap an index
  In order to apply new mapping configurations
  users should be able to
  remap an index

  Scenario: Remap an existing index (changes existing)
    Given I initialized my indices
    And I use alternative configuration folder with changes
    When I call elasticorn "index:remap footest"
    Then I should see message containing '[info] Remapping footest'

  Scenario: Remap an existing index (changes existing)
    Given I initialized my indices
    And I use alternative configuration folder with changes and languages
    When I call elasticorn "index:remap footest"
    Then I should see message containing '[info] Remapping footest in language english'
    Then I should see message containing '[info] Remapping footest in language german'
    Then I should see message containing '[info] Remapping footest in language french'

  Scenario: Remap an existing index (no changes)
    Given I initialized my indices
    When I call elasticorn "index:remap footest"
    Then I should see message containing '[info] No difference between configurations, no remapping done'

  Scenario: Remap an existing index (no changes) - forced
    Given I initialized my indices
    When I call elasticorn "index:remap footest --force"
    Then I should see message containing '[info] No difference between configurations but force given, remapping anyway.'
    Then I should see message containing '[info] Remapping footest'
