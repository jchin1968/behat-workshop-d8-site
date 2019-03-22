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
    $element = $this->getSession()->getPage()->findField($field);

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
}
