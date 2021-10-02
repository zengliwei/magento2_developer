# Developer module for Magento 2

Build some developer tools for testings and make the development easier.

## Examples

### Cross domain request

See controller class `CrazyCat\Developer\Controller\Index\Csrf`.

## Console Commands

### Clean quotes

```sh
bin/magento dev:clean-quotes [options]
```

|Option|Alias|Description|
|---|---|---|
|`--customer_id`|`-c`|Customers ID(s), separated by comma|

### Clean orders

```sh
bin/magento dev:clean-orders [options]
```

|Option|Alias|Description|
|---|---|---|
|`--customer_id`|`-c`|Customers ID(s), separated by comma|

### Place to do debug

```sh
bin/magento dev:debug
```
