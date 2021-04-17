Feature: Compare mapping
  In order to see differences in configurations
  Users should be able to
  compare the configuration on disk with the mapping on the server

  Scenario: Show configured mapping if no changes exist
    Given I initialized my indices
    When I call elasticorn "mapping:compare footest"
    Then I should see:
  """
  [info] No difference between configurations
  """

  Scenario: Show configured mapping containing changes
    Given I initialized my indices
    And I use alternative configuration folder with changes
    When I call elasticorn "mapping:compare footest"
    Then I should see:
"""
-    [name.analyzer] => english
-    [name.type] => text
+    [name.type] => integer
"""

  Scenario: Get help if argument indexName is missing
    Given I initialized my indices
    When I call elasticorn "mapping:compare"
    Then I should see message containing 'Not enough arguments (missing: "indexName")'
    And I should see message containing 'mapping:compare [-c|--config-path CONFIG-PATH] [--] <indexName>'
