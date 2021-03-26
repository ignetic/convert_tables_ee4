Convert Tables EE4 Usage
========================

Convert tables to and from single legacy EE3 table structure to EE4 and EE5
This effectively separates the table data (channel_data) into separate field tables that are used in EE4+

This may also solve a situation where you may have too many table joins. Converting some of the fields back to lagacy format may solve this issue:

```General error: 1116 Too many tables; MySQL can only use 61 tables in a join```


#### Convert Tables: ####

Fields within these tables can be converted:

- Channel Fields (exp_channel_data)
- Category Fields (exp_category_fields_data)
- Member Fields (exp_member_data)


#### Convert Tables Engine Tool: ####

This enables you to convert tables to and from MyISAM and InnoDB.

See: https://docs.expressionengine.com/latest/general/converting-to-innodb.html


#### Caution: ####

This process alters the database structure. Make sure that you backup the database before converting any fields.
