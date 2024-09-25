ORM Feature Flags Storage
================

Using [ApiServiceBundle](https://github.com/TwentytwoLabs/api-service-bundle) to store [Twentytwo Labs Feature Flags](https://github.com/TwentytwoLabs/feature-flag-bundle).

Configuration
-----------

```
# config/packages/twentytwo_labs_feature_flag.yaml
twentytwo_labs_feature_flag:
   managers:
      admin:
         factory: twenty-two-labs.feature-flags.factory.api-service
         options:
            client: 'API_SERVICE_ID'
            collection:
               operationId: COLLECTION_OPERATION_ID
               mapper:       #optional
                  page: page #default
               params:
                  accept: 'application/hal+json'
                  itemsPerPage: 30
            item:
               operationId: ITEM_OPERATION_ID
               mapper:
                  identifier: IDENTIFIER
```

where:
- `API_SERVICE_ID` is an api service id starting with `@`
- `COLLECTION_OPERATION_ID` is operationId for get all features
- `ITEM_OPERATION_ID` is operationId for get one feature by IDENTIFIER
- `IDENTIFIER` is a field in HTTP response
