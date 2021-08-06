# spurCache
To save our precious API calls, spurCache keeps a copy of all the JSON lines from `latest.json` (the Wikimedia feed).

Nice. 👌

## Using the API
Once you have an API key, you can do a `GET` to the endpoint `https://spur.toolforge.org/api/v1`. It'll return some JSON. Simple as.

## Building a query
A query MUST contain:
 - Your API key, in the `key` paramater
 - A valid action, in the `action` parameter
 - Each action will have its own requirements, see below

## Always returned
An API request will always include:
  - `status` - either `success` or `error`
  - `time` - unix timestamp of request

## Valid actions

### `time`
Returns the server time. Not sure why that's useful, but I threw it in there. Don't say I never give you nuffin'

#### Required parameters
  - N/A

#### Example query
`https://spur.toolforge.org/api/v1/?key={key}&action=time`

#### Example return
```json
{
    status: "success",
    time: 1628250880,
    result: {
        date: "2021-08-06 11:54:40.575961",
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
```json
{
    status: "success",
    time: 1628250753,
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

### `live`
The live action is also fairly simple - instead of querying the cache, directly query Spur's context API.
You'll need to use your own Spur API key for this (not the spur tool API key).

#### Required parameters
 - `ip`
 - `spurKey`

#### Example query
`https://spur.toolforge.org/api/v1/?key={key}&action=live&ip=105.112.117.165&spurKey={spurKey}`

#### Example return
```json
{
    status: "success",
    time: 1628250753,
    result: {
        anonymous: true,
        as: {
            number: 36873,
            organization: "Celtel Nigeria Limited t.a ZAIN"
        },
        assignment: {
            exists: false
        },
        deviceBehaviors: {
            exists: false
        },
        devices: {
            estimate: 50
        },
        geoLite: {
            city: "Abuja",
            country: "NG",
            state: "FCT"
        },
        geoPrecision: {
            city: "Asaba",
            country: "NG",
            exists: true,
            hash: "s1her",
            point: {
                latitude: 6.2183,
                longitude: 6.6577,
                radius: 2500
            },
            spread: "100,000km^2",
            state: "Delta"
        },
        ip: "105.112.117.165",
        proxiedTraffic: {
            exists: true,
            proxies: [{
                name: "IPSHARKK_PROXY",
                type: "RESIDENTIAL"
            }]
        },
        similarIPs: {
            exists: true,
            ips: [
                "105.112.101.55",
                "38.91.102.58",
                "105.112.112.243",
                "105.112.124.37",
                "105.112.112.196",
                "105.112.114.26",
                "162.251.61.131",
                "105.112.112.132",
                "105.112.179.37",
                "105.112.101.18",
                "105.112.102.109",
                "105.112.112.96",
                "105.112.124.71",
                "105.112.112.12",
                "105.112.112.175",
                "105.112.112.203",
                "105.112.123.233",
                "105.112.116.142",
                "105.112.112.227",
                "105.112.123.239",
                "105.112.117.14"
            ]
        },
        vpnOperators: {
            exists: false
        },
        wifi: {
            exists: false
        }
    }
}
```

### `stats`
The stats action allows for statistical querying of the data. Makes sense when you think about it.

#### Required parameters
 - `type`
    - `country` - counts per country code
    - `org` - counts per unique org
    - `city` - counts per unique city
    - `count` - total record count

#### Example query
`https://spur.toolforge.org/api/v1/?key={key}&action=stats&type=country`

#### Example return
```json
{
    status: "success",
    time: 1628250753,
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