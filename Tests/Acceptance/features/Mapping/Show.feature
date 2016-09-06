Feature: Show mapping
  In order to check current configuration
  Users should be able to
  view the configuration

  Scenario: Show configured mapping
    When I call elasticorn "mapping:show footest"
    Then I should see:
  """
  tweets:
      properties:
          name:
              type: string
  users:
      properties:
          avatar:
              type: string
          email:
              type: string
              index: not_analyzed
              store: true
          fullname:
              type: string
              index: not_analyzed
              store: true
          id:
              type: integer
          username:
              type: string
              index: not_analyzed
              store: true
  """

  Scenario: Get help if argument indexName is missing
    When I call elasticorn "mapping:show"
    Then I should see message containing 'Not enough arguments (missing: "indexName")'
    And I should see message containing 'mapping:show [-c|--config-path CONFIG-PATH] [--] <indexName>'