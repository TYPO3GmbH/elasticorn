Feature: Show mapping
  In order to check current configuration
  Users should be able to
  view the configuration

  Scenario: Show configured mapping
    Given I initialized my indices
    When I call elasticorn "mapping:show footest"
    Then I should see:
  """
  tweets:
      properties:
          name:
              type: text
              analyzer: english
  users:
      properties:
          avatar:
              type: text
              analyzer: english
          email:
              type: keyword
              store: true
          fullname:
              type: keyword
              store: true
          id:
              type: integer
          username:
              type: keyword
              store: true
  """

  Scenario: Get help if argument indexName is missing
    When I call elasticorn "mapping:show"
    Then I should see message containing 'Not enough arguments (missing: "indexName")'
    And I should see message containing 'mapping:show [-c|--config-path CONFIG-PATH] [--] <indexName>'