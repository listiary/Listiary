-- 4. Housekeeping
-- This will link Describe item ids to filenames they are defined in.
-- N said this compound primary key creates an index, but we need an
-- additional index - 'idx_item_id' - to efficiently backward search.
CREATE TABLE housekeeping_itemid_filename (
    filename VARCHAR(333) NOT NULL,
    item_id VARCHAR(333) NOT NULL,
    PRIMARY KEY (filename(150), item_id(150))
);

-- index to speed up lookups by item_id
CREATE INDEX idx_item_id ON housekeeping_itemid_filename (item_id);

-- This will link files to files needed when loading them.
-- N said this compound primary key creates an index, but we need an
-- additional index - 'idx_item_id' - to efficiently backward search.
CREATE TABLE housekeeping_filename_related (
    filename VARCHAR(333) NOT NULL,
    related_filename VARCHAR(333) NOT NULL,
    PRIMARY KEY (filename(150), related_filename(150))
);

-- index to speed up lookups by related_filename
CREATE INDEX idx_related_filename ON housekeeping_filename_related (related_filename);