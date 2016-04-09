#!/usr/bin/python2
"""Print SQL queries for migrating FIC production DB to a local dev server."""


PREFIX = '3uOgy46w'
ORIGINAL_URL = 'http://www.ic.org'
LOCAL_URL = 'http://localhost:9000'

print """
UPDATE {0}_options
SET option_value = replace(option_value, '{1}', '{2}')
WHERE option_name = 'home' OR option_name = 'siteurl';
""".format(PREFIX, ORIGINAL_URL, LOCAL_URL)

print """
UPDATE {0}_posts SET guid = REPLACE (guid, '{1}', '{2}');
""".format(PREFIX, ORIGINAL_URL, LOCAL_URL)

print """
UPDATE {0}_posts
SET post_content = REPLACE (post_content, '{1}', '{2}');
""".format(PREFIX, ORIGINAL_URL, LOCAL_URL)

print """
UPDATE {0}_postmeta SET meta_value = REPLACE (meta_value, '{1}','{2}');
""".format(PREFIX, ORIGINAL_URL, LOCAL_URL)
