<?php

use Drupal\DrupalExtension\Context\MinkContext;
use Behat\Mink\Exception\ExpectationException;


class MyMinkContext extends MinkContext {

  /**
   * Override MinkExtension\Context\MinkContext::fillField.
   *
   * Resolves the issue where entering text in a formatted textarea field
   * (i.e. CKEditor) would fail when executing the test via Selenium.
   *
   * @throws ExpectationException
   */
  public function fillField($field, $value) {
    // Locate the field on the page.
    // $element = $this->getSession()->getPage()->findField($field);  // Default way to locate a field. Fails for date popup.
    $element  = $this->findField($field);                             // Our new way to handle date popup.

    // Throw an error if the field cannot be found.
    if (empty($element)) {
      throw new ExpectationException('Can not find field: ' . $field, $this->getSession());
    }

    // Get the field ID. Throw an error if it cannot be found.
    $field_id = $element->getAttribute('id');
    if (empty($field_id)) {
      throw new ExpectationException('Can not find id for field: ' . $field, $this->getSession());
    }

    // Check if a corresponding CKEditor field exists.
    // NOTE: For a formatted textarea field using CKEditor, a div block containing
    // a standard HTML textarea tag is rendered but hidden using css. Another div
    // block (a sibling to the previous one) contains a CKEditor iframe which is
    // what the user interacts with.
    $cke_field_id = 'cke_' . $field_id;
    $cke_element = $this->getSession()->getPage()->find('named', ['id', $cke_field_id]);
    if (empty($cke_element)) {
      // CKEditor object was not found. This is either (1) not a textarea field or
      // (2) a textarea field without CKEditor. So, just use the default method
      // to update the field.
      parent::fillField($field_id, $value);
    } else {
      // CKEditor object was found. Update the field using javascript.
      $this->getSession()->executeScript("CKEDITOR.instances[\"$field_id\"].setData(\"$value\");");
    }
  }

  /**
   * Find a field on a page for a given locator.
   *
   * By default, the locator can be an id or name attribute to an <input> tag.
   * It can also be the field label which is rendered as a <label> tag just
   * before the field <input> tag.
   *
   * However, for certain fields (i.e. date popup), the <label> tag is hidden
   * (using css) but more importantly, the label text does not correspond to
   * what the user set the label to be. Instead, what a user sees as the label
   * is rendered as an <h4 class="label"> tag. This has no impact to the end
   * user from a labeling perspective but Behat operations using the Drupal
   * Extension fails.
   */
  public function findField($locator) {
    // Default way to find a field.
    $element = $this->getSession()->getPage()->findField($locator);

    // If field was not found, then try another approach.
    if (empty($element)) {
      // Search for h4 tags with class "label".
      $h4_elements = $this->getSession()->getPage()->findAll('css', 'h4.label');

      // Iterate through the list of h4.label tags and see if their text
      // matches the $locator value.
      foreach ($h4_elements as $h4_element) {
        if ($h4_element->getText() == $locator) {
          // Found the matching h4 locator. Get the input tag that corresponds
          // to it and break out of the foreach loop.
          $element = $h4_element->getParent()->find('css', 'input');
          break;
        }
      }
    }
    return $element;
  }

}
