@javascript
Feature: Homepage
  In order to have a good user experience
  As a user
  I want to have a starting point for my journey

  Scenario: Welcome
    Given I am an anonymous user
    When I am on the homepage
    Then I should see the heading "Welcome to Behat Workshop"
