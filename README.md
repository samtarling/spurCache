# spurCache
To save our precious API calls, spurCache keeps a copy of all the JSON lines from `latest.json` (the Wikimedia feed).

Nice.

## Using the API
Once you have an API key, you can do a simple `GET` to the endpoint `https://spur.toolforge.org/api/v1`. It'll return some JSON. Simple as.

## Building a query
A query MUST contain:
 - Your API key, in the `key` paramater
 - A valid action, in the `action` parameter
 - Each action will have its own requirements, see below

## Valid actions

### `time`
Returns the server time. Not sure why that's useful, but I threw it in there. Don't say I never give you nuffin'

#### Required parameters
  - N/A

#### Example query
`https://spur.toolforge.org/api/v1/?key={key}&action=time`

#### Example return
```
{
    status: "success",
    result: {
        date: "2021-08-03 18:04:37.326139",
        timezone_type: 3,
        timezone: "UTC"
    }
}
```

### `query`
The query action is fairly simple - go get some information from the cache about an IP address.

#### Required parameters
 - `ip`

#### Example query
`https://spur.toolforge.org/api/v1/?key={key}&action=query&ip=105.112.117.165`

#### Example return
```
{
    status: "success",
    result: {
        id: "6f482369b7715a73ea0bbd70d6aaa679efbf2f64",
        cache_timestamp: {
            date: "2021-08-03 13:28:27.000000",
            timezone_type: 3,
            timezone: "UTC"
        },
        IP: "105.112.117.165",
        user_count: 100,
        maxmind_city: "Abuja",
        maxmind_cc: "NG",
        maxmind_subdivision: "FCT",
        services: [
            "IPSHARKK_PROXY"
        ],
        org: "Airtel Networks Limited",
        getipintel_score: 0,
        raw_feed_result: "{\"user_count\": 100, \"ip\": \"105.112.117.165\", \"maxmind_city\": \"Abuja\", \"maxmind_cc\": \"NG\", \"services\": [\"IPSHARKK_PROXY\"], \"org\": \"Airtel Networks Limited\", \"maxmind_subdivision\": \"FCT\"}",
        do_not_purge: false,
        hidden: false,
        expired: false
    }
}
```

### `stats`
The stats action allows for statistical querying of the data. Makes sense when you think about it.

#### Required parameters
 - `type`
    - `country`
    - `org`
    - `city`

#### Example query
`https://spur.toolforge.org/api/v1/?key={key}&action=stats&type=country`

#### Example return
```
{
    status: "success",
    result: [
        {
            maxmind_cc: "GH",
            count: 5354
        },
        {
            maxmind_cc: "EG",
            count: 4724
        },
        {
            maxmind_cc: "VE",
            count: 3212
        }
    ]
}
```