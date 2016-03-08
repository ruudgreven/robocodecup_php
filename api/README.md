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
                        "fullname": "pgr.team.EP_BaseTeam",
                        "pool_name": "Enschede",
                        "authorname": "",
                        "pool_id": "E",
                        "name": "EP_BaseTeam",
                        "id": "EHI1VSx_1",
                        "pool_description": "Alle klassen uit Enschede",
                        "description": "Pauls team, jaja."
                    },
                    ...
                ],
                "description": "Alle klassen uit Enschede"
            }
        }
    ]
}

```

## GET /team/__TEAMID__.json
Get  all the information from a team
Arguments:
- teamid, The id of the team you want to view.

#### Example output
```
{
    "status": "ok",
    "response": [
        {
            "fullname": "pgr.team.EP_BaseTeam",
            "authorname": "",
            "name": "EP_BaseTeam (de 2e)",
            "secretkey": null,
            "id": "EHI1VSx_1",
            "competition_id": 1,
            "description": "Pauls team, jaja."
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


### GET /round/__NUM__/ranking.json
Get the ranking in the given round in the current competition, ordened by the pools. The ranking is the sum of alle scores in this round per team per pool

In the array scores there is a list of teamproperties (id, name) combined with the scores of this team

Arguments:
- num, The number of the round you want to view.

#### Example output
```
{
    "status": "ok",
    "response": [
        {
            "scores": [
                {
                    "survivalscore": 11750,
                    "totalscore": 28763,
                    "ramdamage": 5774,
                    "bulletdamage": 7404,
                    "survivalbonus": 1520,
                    "firsts": 16,
                    "totalbattles": 2,
                    "name": "EP_BaseTeam",
                    "id": "x_1",
                    "thirds": 0,
                    "seconds": 5,
                    "bulletbonus": 1338,
                    "rambonus": 978
                },
               ...
            ],
            "name": "Enschede",
            "id": "E"
        },
        ...
    ]
}
```

when there is no info for this round
```
{
    "status": "ok",
    "response": [

    ]
}
```



### GET /round/__NUM__/__TEAMID__/battles.json
Received all the battles in the given round for the given team.

Arguments:
- num, The number of the round you want to view.
- teamid, The id of the team you want to view.

#### Example output
```
{
    "status": "ok",
    "response": [
        {
            "pool_name": "Enschede",
            "scores": [
                {
                    "survivalscore": 5850,
                    "totalscore": 14181,
                    "team_name": "EP_BaseTeam (de 2e)",
                    "totalpercentage": 78,
                    "ramdamage": 3042,
                    "bulletdamage": 3471,
                    "survivalbonus": 760,
                    "firsts": 7,
                    "thirds": 0,
                    "seconds": 3,
                    "bulletbonus": 680,
                    "rambonus": 379,
                    "team_id": "EHI1VSx_1"
                },
                ...
            ],
            "pool_id": "E",
            "datetime": "2016-03-01 08:31:15",
            "id": 27
        },
        ...
    ]
}

```

when there is no info for this round or team
```
{
    "status": "ok",
    "response": [

    ]
}
```



### GET /messages.json
Received all messages for this competition.

#### Example output
```
{
    "status": "ok",
    "response": [
        {
            "title": "Plaatje",
            "actiontitle": "",
            "imageurl": "https://material.angularjs.org/latest/img/icons/angular-logo.svg",
            "actionlink": "",
            "id": 5,
            "date": "2016-03-08 00:00:00",
            "showfrom": "2016-03-08 00:00:00",
            "featuredtill": "2016-05-05 00:00:00",
            "showtill": "0000-00-00 00:00:00",
            "message": "Haar gebit was een plaatje",
            "competition_id": 1,
            "featuredfrom": "2016-03-08 00:00:00"
        }
    ]
}

```



### GET /messages/featured.json
Received all featured messages for this competition.

#### Example output
```
{
    "status": "ok",
    "response": [
        {
            "title": "Plaatje",
            "actiontitle": "",
            "imageurl": "https://material.angularjs.org/latest/img/icons/angular-logo.svg",
            "actionlink": "",
            "id": 5,
            "date": "2016-03-08 00:00:00",
            "showfrom": "2016-03-08 00:00:00",
            "featuredtill": "2016-05-05 00:00:00",
            "showtill": "0000-00-00 00:00:00",
            "message": "Haar gebit was een plaatje",
            "competition_id": 1,
            "featuredfrom": "2016-03-08 00:00:00"
        }
    ]
}
