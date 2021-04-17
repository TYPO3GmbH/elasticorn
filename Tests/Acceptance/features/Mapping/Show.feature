Feature: Show mapping
  In order to check current configuration
  Users should be able to
  view the configuration

  Scenario: Show configured mapping
    Given I initialized my indices
    When I call elasticorn "mapping:show footest"
    Then I should see:
  """
  properties:
      name:
          type: text
          analyzer: english
  """

  Scenario: Get help if argument indexName is missing
    When I call elasticorn "mapping:show"
    Then I should see message containing 'Not enough arguments (missing: "indexName")'
    And I should see message containing 'mapping:show [-c|--config-path CONFIG-PATH] [--] <indexName>'
