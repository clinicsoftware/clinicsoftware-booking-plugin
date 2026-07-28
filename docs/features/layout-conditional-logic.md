# Layout Conditional Logic

Conditional logic allows you to show or hide fields, blocks, or entire pages based on user input. This feature is useful
for creating dynamic forms that adapt to user input.

## Conditions

A field can have many conditions. Each condition is a combination of an operator (read below for options) and a value.
The operator is used to compare the field's value with the condition's value. If the comparison is true, the condition
is met and the action is executed.

For the action to execute all conditions must be true.

Conditions may be combined with `AND` or `OR` operators. If a field has multiple conditions, you can choose to execute
the action if all conditions are met (`AND`) or if any condition is met (`OR`).

### Operators

- **Equals**: The field's value is equal to the condition's value.
- **Not Equals**: The field's value is not equal to the condition's value.
- **Contains**: The field's value contains the condition's value.
- **Does Not Contain**: The field's value does not contain the condition's value.
- **Starts With**: The field's value starts with the condition's value.
- **Ends With**: The field's value ends with the condition's value.
- **Is Empty**: The field's value is empty.
- **Is Not Empty**: The field's value is not empty.
- **Content Length Equals**: The field's value length is equal to the condition's value.
- **Content Length Not Equals**: The field's value length is not equal to the condition's value.
- **Content Length Greater Than**: The field's value length is greater than the condition's value.
- **Content Length Less Than**: The field's value length is less than the condition's value.
- **Content Length Greater Than or Equal**: The field's value length is greater than or equal to the condition's value.
- **Content Length Less Than or Equal**: The field's value length is less than or equal to the condition's value.

## Actions

- **Hide Block**: Hide the block if the conditions are met.
- **Disable Block**: Disable the block if the conditions are met.
- **Require Answer**: Require the block if the conditions are met.
