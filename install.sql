-- POI data in user table
ALTER TABLE wcf1_user ADD poiPois INT(10) NOT NULL DEFAULT 0;
ALTER TABLE wcf1_user ADD INDEX poiPois (poiPois);

-- POI
DROP TABLE IF EXISTS poi1_poi;
CREATE TABLE poi1_poi (
	poiID				INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	userID				INT(10),
	username			VARCHAR(255) NOT NULL DEFAULT '',
	
	elevation			INT(10) NOT NULL DEFAULT 0,
	location			VARCHAR(255) NOT NULL DEFAULT '',
	latitude			FLOAT(10,7) NOT NULL DEFAULT 0.0,
	longitude			FLOAT(10,7) NOT NULL DEFAULT 0.0,
	
	message				MEDIUMTEXT,
	subject				VARCHAR(255) NOT NULL DEFAULT '',
	teaser				TEXT,
	enableHtml			TINYINT(1) NOT NULL DEFAULT 0,
	enableComments		TINYINT(1) NOT NULL DEFAULT 1,
	hasEmbeddedObjects	TINYINT(1) NOT NULL DEFAULT 0,
	
	time				INT(10) NOT NULL DEFAULT 0,
	lastChangeTime		INT(10) NOT NULL DEFAULT 0,
	lastVersionTime		INT(10) NOT NULL DEFAULT 0,
	
	categoryID			INT(10),
	languageID			INT(10),
	
	isDeleted			TINYINT(1) NOT NULL DEFAULT 0,
	deleteTime			INT(10) NOT NULL DEFAULT 0,
	isDisabled			TINYINT(1) NOT NULL DEFAULT 0,
	isFeatured			TINYINT(1) NOT NULL DEFAULT 0,
	
	attachments			SMALLINT(5) NOT NULL DEFAULT 0,
	comments			SMALLINT(5) NOT NULL DEFAULT 0,
	cumulativeLikes		MEDIUMINT(7) NOT NULL DEFAULT 0,
	ipAddress			VARCHAR(39) NOT NULL DEFAULT '',
	views				MEDIUMINT(7) NOT NULL DEFAULT 0,
	visits				MEDIUMINT(7) NOT NULL DEFAULT 0,
	
	coverPhotoID		INT(10),
	
	KEY (comments),
	KEY (cumulativeLikes),
	KEY (time),
	KEY (lastChangeTime),
	KEY (views),
	KEY (visits)
	);

DROP TABLE IF EXISTS poi1_geocache;
CREATE TABLE poi1_geocache (
	geocacheID		INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	hash			VARCHAR(32) NOT NULL,
	location		VARCHAR(255) NOT NULL,
	lat				FLOAT(10,7) NOT NULL,
	lng				FLOAT(10,7) NOT NULL,
	time			INT(10) NOT NULL,
	type			TINYINT(1) NOT NULL DEFAULT 0,
	
	UNIQUE KEY (hash)
);

DROP TABLE IF EXISTS poi1_poi_option;
CREATE TABLE poi1_poi_option (
	optionID				INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	optionTitle				VARCHAR(255) NOT NULL DEFAULT '',
	optionDescription		TEXT,
	optionType				VARCHAR(255) NOT NULL DEFAULT '',
	defaultValue			MEDIUMTEXT,
	validationPattern		TEXT,
	selectOptions			MEDIUMTEXT,
	required				TINYINT(1) NOT NULL DEFAULT 0,
	showOrder				INT(10) NOT NULL DEFAULT 0,
	isDisabled				TINYINT(1) NOT NULL DEFAULT 0
);

DROP TABLE IF EXISTS poi1_poi_option_value;
CREATE TABLE poi1_poi_option_value (
	poiID					INT(10) NOT NULL,
	optionID				INT(10) NOT NULL,
	optionValue				MEDIUMTEXT NOT NULL,
	
	UNIQUE KEY				groupID (poiID, optionID)
);

DROP TABLE IF EXISTS poi1_poi_visit;
CREATE TABLE poi1_poi_visit (
	poiID					INT(10) NOT NULL,
	userID					INT(10) NOT NULL,
	time					INT(10) NOT NULL DEFAULT 0,
	
	UNIQUE KEY				(poiID, userID)
);

DROP TABLE IF EXISTS poi1_cover_photo;
CREATE TABLE poi1_cover_photo (
	coverPhotoID			INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	poiID					INT(10),
	userID					INT(10),
	time					INT(10) NOT NULL,
	fileExtension			VARCHAR(4) NOT NULL,
	fileHash				CHAR(40) NOT NULL
);

ALTER TABLE poi1_poi ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE SET NULL;
ALTER TABLE poi1_poi ADD FOREIGN KEY (languageID) REFERENCES wcf1_language (languageID) ON DELETE SET NULL;
ALTER TABLE poi1_poi ADD FOREIGN KEY (categoryID) REFERENCES wcf1_category (categoryID) ON DELETE SET NULL;

ALTER TABLE poi1_poi_option_value ADD FOREIGN KEY (poiID) REFERENCES poi1_poi (poiID) ON DELETE CASCADE;
ALTER TABLE poi1_poi_option_value ADD FOREIGN KEY (optionID) REFERENCES poi1_poi_option (optionID) ON DELETE CASCADE;

ALTER TABLE poi1_poi_visit ADD FOREIGN KEY (poiID) REFERENCES poi1_poi (poiID) ON DELETE CASCADE;
ALTER TABLE poi1_poi_visit ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;

ALTER TABLE poi1_cover_photo ADD FOREIGN KEY (poiID) REFERENCES poi1_poi (poiID) ON DELETE SET NULL;
ALTER TABLE poi1_cover_photo ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE SET NULL;
ALTER TABLE poi1_poi ADD FOREIGN KEY (coverPhotoID) REFERENCES poi1_cover_photo (coverPhotoID) ON DELETE SET NULL;

