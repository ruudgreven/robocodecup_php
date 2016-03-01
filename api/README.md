# RobocodeCup API

## API Documentation

### GET /competition.json
Get the current competition.

#### Example output
```
{
    "status": "ok",
    "response": [
        {
            "name": "USEB 2016",
            "id": "1"
        }
    ]
}
```



### GET /pool.json
Get  all pools from the current competition with their associated teams.

#### Example output
```
{
    "status": "ok",
    "response": [
        {
            "E": {
                "name": "Enschede",
                "id": "E",
                "teams": [
                    {
                        "fullname": "ruudsteam.RuudsTeam",
                        "pool_name": "Enschede",
                        "authorname": "Ruud Greven",
                        "pool_id": "E",
                        "name": "RuudsTeam",
                        "id": "EHI1VSx_3",
                        "description": "Ruud roeleert!"
                    },
                    ...
                ]
            }
        }
    ]
}

```



### GET /round.json
Get  all rounds from the current competition with a reference to the previous, current and next round.
The reference to previous and next can be -1 if there is no previous or next round defined

#### Example output
```
{
    "status": "ok",
    "response": [
        {
            "previous": -1,
            "current": 3,
            "next": 4,
            "rounds": [
                {
                    "startdate": "2016-02-29",
                    "enddate": "2016-03-06",
                    "number": "3"
                },
                ...
            ]
        }
    ]
}

```



### GET /round/__NUM__/team.json
Get a list of teams that comes out in the given round in the current competition, ordened by pool.

Arguments:
- num, The number of the round you want to view.

#### Example output
```
{
    "status": "ok",
    "response": [
        {
            "E": {
                "name": "Enschede",
                "id": "E",
                "teams": [
                    {
                        "fullname": "pgr.team.EP_BaseTeam",
                        "pool_name": "Enschede",
                        "authorname": "",
                        "pool_id": "E",
                        "name": "EP_BaseTeam",
                        "id": "EHI1VSx_1",
                        "description": "Pauls team, jaja."
                    },
                    ...
                ]
            }
        }
    ]
}


```


when there is no info for this round

```
{
    "status": "ok",
    "response": [
        [

        ]
    ]
}
```




### GET /round/__NUM__/battles.json
Get a list of all played battles in the given round in the current competition, ordened by the pools

Under every pool there is named array with battles, every battle has a unique number as name and the battle itself as value.

A battle is an object with an id, a datetime that it's played, a flag that describes whether or not it's official or not. Inside a battle there is an array with scores.

In the array scores there is a list of teamproperties (id, name) combined with the scores of this team


Arguments:
- num, The number of the round you want to view.

#### Example output
```
{
    "status": "ok",
    "response": [
        {
            "E": {
                "name": "Enschede",
                "id": "E",
                "battles": {
                    "32": {
                        "scores": [
                            {
                                "rank": "1",
                                "survivalscore": "5550",
                                "totalscore": "14688",
                                "totalpercentage": "78",
                                "ramdamage": "3326",
                                "bulletdamage": "3812",
                                "survivalbonus": "840",
                                "firsts": "9",
                                "name": "EP_BaseTeam",
                                "id": "EHI1VSx_1",
                                "thirds": "0",
                                "seconds": "1",
                                "bulletbonus": "822",
                                "rambonus": "338"
                            },
                            ...
                        ],
                        "official": "1",
                        "datetime": "2016-03-01 10:33:04",
                        "id": "32"
                    },
                    ...
                }
            }
        }
    ]
}
```

when there is no info for this round
```
{
    "status": "ok",
    "response": [
        [

        ]
    ]
}
```