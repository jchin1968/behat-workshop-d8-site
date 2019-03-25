@api @javascript
Feature: Request for training
  In order to further my skills
  As an employee
  I would like to request training courses

  Background:
    Given users:
      | name   | email           | roles   | status | field_manager |
      | Moira  | moira@test.bot  | Manager | 1      |               |
      | Joe    | joe@test.bot    | Manager | 1      | Moira         |
      | Jill   | joe@test.bot    | Manager | 1      | Moira         |
      | Oliver | oliver@test.bot | Staff   | 1      | Joe           |
      | Martin | martin@test.bot | Staff   | 1      | Jill          |

  Scenario: Request form accessible to staff users
    Given I am logged in as a "Staff"
    When I visit "node/add/training_request"
    Then I should see the heading "Create Training Request"

  Scenario: Request form not accessible to anonymous users
    Given I am an anonymous user
    When I visit "node/add/training_request"
    Then I should see the heading "Access denied"
    And I should see the text "You are not authorized to access this page."

  Scenario: Submit Form
    Given I am logged in as "Oliver"
    And I visit "node/add/training_request"
    When I fill in the following:
      | Short Description | Behat Workshop                      |
      | Purpose           | Need to implement automated testing |
      | Manager           | Joe                                 |
      | Start Date        | 04/20/2019                          |
      | End Date          | 04/22/2019                          |
      | Estimated Cost    | 75.00                               |
    And I press the "Save" button
    Then I should see the success message "Training Request Behat Workshop has been created."
    And I should see the link "Joe" in the "manager" region
    And I should see "$75.00SGD" in the "estimated_cost" region

  Scenario Outline: Auto-filled fields
    Given I am logged in as "<user>"
    When I visit "node/add/training_request"
    Then the "Manager" field should contain "<manager>"
    Examples:
      | user   | manager |
      | Martin | Jill    |
      | Oliver | Joe     |
      | Jill   | Moira   |
      | Joe    | Moira   |