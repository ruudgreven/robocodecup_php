/**
Describes a competition, a competition has a name
 */
CREATE TABLE competition (
  id INT NOT NULL AUTO_INCREMENT,
  name VARCHAR(64),
  PRIMARY KEY (id)
);

/**
Describes a pool. The pool has a unique name in it's competition
 */
CREATE TABLE pool (
  id VARCHAR(16) NOT NULL,
  competition_id INT,
  name VARCHAR(64),
  description VARCHAR(255),
  PRIMARY KEY (competition_id, id),
  FOREIGN KEY (competition_id) REFERENCES competition(id)
);

/**
A team has a unique id inside a competition
 */
CREATE TABLE team (
  id VARCHAR(16) NOT NULL,
  competition_id INT,
  secretkey VARCHAR(6),
  name VARCHAR(64),
  authorname VARCHAR(64),
  description VARCHAR(255),
  PRIMARY KEY (id, competition_id),
  FOREIGN KEY (competition_id) REFERENCES competition(id)
);

/**
Describes the teams in a pool, a team has unique id in the competition. A team can join in multiple pools
 */
CREATE TABLE poolteams (
  competition_id INT,
  pool_id VARCHAR(16),
  team_id VARCHAR(16),
  PRIMARY KEY (competition_id, pool_id, team_id),
  FOREIGN KEY (competition_id) REFERENCES competition(id),
  FOREIGN KEY (competition_id, pool_id) REFERENCES pool(competition_id, id),
  FOREIGN KEY (competition_id, team_id) REFERENCES team(competition_id, id)
);

/**
Describes a round in a competition. It belongs to a competition, and it has a number (in order)
*/
CREATE TABLE round (
  number INT,
  competition_id INT,
  startdate DATE,
  enddate DATE,
  PRIMARY KEY (competition_id, number),
  FOREIGN KEY (competition_id) REFERENCES competition(id)
);

/**
A battle has a globally unique id. It will be played in a competition and a pool, and it will be played in a certain round
 */
CREATE TABLE battle (
  id INT NOT NULL AUTO_INCREMENT,
  competition_id INT,
  pool_id VARCHAR(16),
  round_number INT,
  datetime DATETIME,
  official BOOL,
  PRIMARY KEY (id),
  FOREIGN KEY (competition_id) REFERENCES competition(id),
  FOREIGN KEY (competition_id, pool_id) REFERENCES pool(competition_id, id),
  FOREIGN KEY (competition_id, round_number) REFERENCES round(competition_id, number)
);

CREATE TABLE battlescores (
  competition_id INT NOT NULL,
  pool_id VARCHAR(16) NOT NULL,
  battle_id INT NOT NULL,
  team_id VARCHAR(16) NOT NULL,
  totalscore INT,
  totalpercentage INT,
  survivalscore INT,
  survivalbonus INT,
  bulletdamage INT,
  bulletbonus INT,
  ramdamage INT,
  rambonus INT,
  firsts INT,
  seconds INT,
  thirds INT,
  PRIMARY KEY (competition_id, pool_id, battle_id, team_id),
  FOREIGN KEY (competition_id) REFERENCES competition(id),
  FOREIGN KEY (competition_id, pool_id) REFERENCES pool(competition_id, id),
  FOREIGN KEY (battle_id) REFERENCES battle(id),
  FOREIGN KEY (competition_id, team_id) REFERENCES team(competition_id, id)
)