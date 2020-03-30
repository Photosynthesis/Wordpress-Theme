#!/usr/bin/env python
"""
Create a CSV export containing Emails for Listing Editors & Contacts.

Email Addresses and Community Names are pulled from data in a Formidable Form.
Editor Emails are derived from the Author of the associated Wordpress Post
while Contact Emails are from a Contact Email field.

Simple validation and uniqueness checks are run on the list of email addreses.

The CSV file is written to ``export.csv`` in the current working directory and
has the following fields:

    email,community name,role

Where ``role`` is either ``contact`` or ``editor``.

Written For The FIC by Pavan Rikhi<pavan@ic.org> on 8/17/2014.

"""

import datetime
import re

from db import get_cursor

# The Regular Expression Used to Validate Email Addresses
EMAIL_REGEX = re.compile(r'[^@]+@[^@]+\.[^@]+')

# The ID Number of the Formidable Form
FORM_ID = 2

# The ID Number of the Formidable Field Containing the Contact Person's Email.
CONTACT_EMAIL_FIELD_ID = 199

# The ID Number of the Formidable Field Containg the Contact Person's Name.
CONTACT_NAME_FIELD_ID = 202

# The ID Number of the Formidable Field Containing the Backup Email.
BACKUP_EMAIL_FIELD_ID = 284

# The ID Number of the Formidable Field of the Year the Listing was Made
LISTING_CREATED_FIELD_ID = 725

LISTING_TYPE_FIELD_ID = 262

FIC_MEMBERSHIP_FIELD_ID = 933

# Whether or not to include all emails of a community, or just one
INCLUDE_ALL_EMAILS = True

# Whether or not to add the years since the community has been created as the
# 3rd field in the CSV
INCLUDE_YEARS_SINCE_CREATED = False

# Filer out communities that have been last updated after this date. Set to
# None to disable filtering.
FILTER_LAST_UPDATED_AFTER = None

# Filter out communities that have been last updated before this date. Set to
# None to disable filtering.
FILTER_LAST_UPDATED_BEFORE = None

# Filter out communities that not of this type.
FILTER_COMMUNITY_TYPE = None


def main():
    """Retrieve the Emails of Listing Contacts and Generate a CSV File."""
    listing_rows = get_listing_contact_rows()
    unique_csv_lines = make_unique_csv_lines(listing_rows)
    write_csv_file(unique_csv_lines)


def get_listing_contact_rows():
    """Retrieve the Listing Contacts Query Result."""
    listing_contacts_query = """
        SELECT * FROM
                  (SELECT form_id, id, post_id, user_id, updated_at, is_draft
                   FROM 3uOgy46w_frm_items AS items
                   WHERE items.form_id={0}) AS items
        LEFT JOIN (SELECT meta_value AS contact_email, item_id
                   FROM 3uOgy46w_frm_item_metas
                   WHERE field_id={1})
             AS cemail_metas ON items.id=cemail_metas.item_id
        LEFT JOIN (SELECT meta_value AS contact_name, item_id
                   FROM 3uOgy46w_frm_item_metas
                   WHERE field_id={2})
             AS cname_metas ON items.id=cname_metas.item_id
        LEFT JOIN (SELECT meta_value AS created_date, item_id
                   FROM 3uOgy46w_frm_item_metas
                   WHERE field_id={3})
             AS created_metas ON items.id=created_metas.item_id
        LEFT JOIN (SELECT meta_value AS backup_email, item_id
                   FROM 3uOgy46w_frm_item_metas
                   WHERE field_id={4})
             AS backup_emails ON items.id=backup_emails.item_id
        LEFT JOIN (SELECT meta_value AS community_type, item_id
                   FROM 3uOgy46w_frm_item_metas
                   WHERE field_id={5})
             AS community_types ON items.id=community_types.item_id
        LEFT JOIN (SELECT meta_value AS is_member, item_id
                   FROM 3uOgy46w_frm_item_metas
                   WHERE field_id={6})
             AS is_member ON items.id=is_member.item_id
        RIGHT JOIN (SELECT post_title, post_author, ID, post_type, post_status FROM 3uOgy46w_posts WHERE post_type='directory' AND post_status='publish')
             AS posts ON items.post_id=posts.ID
        LEFT JOIN (SELECT user_email, display_name, ID FROM 3uOgy46w_users)
            AS users ON users.ID=posts.post_author
        WHERE items.form_id={0} AND items.is_draft=0""".format(FORM_ID, CONTACT_EMAIL_FIELD_ID,
                                          CONTACT_NAME_FIELD_ID,
                                          LISTING_CREATED_FIELD_ID,
                                          BACKUP_EMAIL_FIELD_ID,
                                          LISTING_TYPE_FIELD_ID,
                                          FIC_MEMBERSHIP_FIELD_ID)
    cursor = get_cursor()
    cursor.execute(listing_contacts_query)
    listing_rows = cursor.fetchall()
    return listing_rows


def make_unique_csv_lines(rows):
    """Make CSV Lines from Listing Rows."""
    filtered_rows = filter_listing_rows(rows)
    csv_rows = []
    [csv_rows.extend(make_csv_lines(row)) for row in filtered_rows]
    return [u'{0}'.format(row) for row in ensure_unique_emails(csv_rows)]


def filter_listing_rows(rows):
    if FILTER_LAST_UPDATED_AFTER is not None:
        rows = [row for row in rows
                if row['updated_at'] > FILTER_LAST_UPDATED_AFTER]
    if FILTER_LAST_UPDATED_BEFORE is not None:
        rows = [row for row in rows
                if row['updated_at'] < FILTER_LAST_UPDATED_BEFORE]
    if FILTER_COMMUNITY_TYPE is not None:
        rows = [row for row in rows
                if FILTER_COMMUNITY_TYPE.lower() in row['community_type'].lower()]
    return rows


def make_csv_lines(listing_row):
    """Create CSV lines of `email,community name,role` from a Listing row."""
    output = []
    community_name = clean(listing_row["post_title"])
    if community_name is None:
        return output
    created_years = clean(clean_date(listing_row["created_date"]))

    if listing_row["is_member"] is None:
        is_member = "no"
    else:
        is_member = "yes" if listing_row["is_member"] == "Yes" else "no"

    contact_email = clean(listing_row["contact_email"])
    if is_valid_email(contact_email):
        contact_name = clean(clean_name(listing_row["contact_name"]))
        output.append(create_csv_line(contact_email, contact_name, created_years,
                                  community_name, 'contact', is_member))
    if contact_email in [None, ''] or INCLUDE_ALL_EMAILS:
        editor_email = clean(listing_row["user_email"])
        if is_valid_email(editor_email) and editor_email != contact_email:
            editor_name = (clean(clean_name(listing_row["display_name"]))
                           if not INCLUDE_ALL_EMAILS else '')
            output.append(create_csv_line(editor_email, editor_name, created_years,
                                      community_name, 'editor', is_member))
        if not is_valid_email(editor_email) or INCLUDE_ALL_EMAILS:
            backup_email = clean(listing_row["backup_email"])
            backup_name = (clean(clean_name(listing_row["display_name"]))
                           if not INCLUDE_ALL_EMAILS else '')
            if (is_valid_email(backup_email) and backup_email not in
                    [contact_email, editor_email]):
                output.append(create_csv_line(
                    backup_email, backup_name, created_years, community_name,
                    'backup', is_member))
    return output


def clean(field_value):
    """Cleanup a Field, Removing Quotations or Additional Commas."""
    if field_value is None:
        return None
    return field_value.replace(',', '').replace("'", '').replace('"', '')


def clean_date(date_string):
    """Validate a YYYY-MM-DD string and return the Year or an empty string."""
    if date_string is None:
        return ''
    parts = date_string.split('-')
    if len(parts) != 3:
        return ''
    try:
        year = parts[0]
        return year if year != '1970' else ''
    except ValueError:
        return ''


def is_valid_email(email_address):
    """Check if an E-Mail Address is Valid."""
    return (email_address is not None and
            ' ' not in email_address and
            EMAIL_REGEX.match(email_address) is not None)


def clean_name(name):
    """Remove the last name."""
    if name is None:
        return ''
    parts = name.split(' ')
    if len(parts) > 1:
        return ' '.join(parts[:-1])
    else:
        return name


def create_csv_line(email, name, years, community, role, is_member):
    """Create a CSV line from an email/community."""
    if INCLUDE_YEARS_SINCE_CREATED:
        return u'{0},{1},{2},{3},{4},{5}'.format(
            email, name, years, community, role, is_member)
    else:
        return u'{0},{1},{2},{3},{4}'.format(email, name, community, role, is_member)


def ensure_unique_emails(csv_lines):
    """Make sure each CSV line has a unique email address."""
    unique_csv_lines = list()
    used_emails = list()
    for line in csv_lines:
        if line is None:
            continue
        email = line.split(',')[0].lower()
        if email not in used_emails:
            unique_csv_lines.append(line)
            used_emails.append(email)
    return unique_csv_lines


def write_csv_file(csv_lines):
    """Write the Lines to ``export.csv`` in the Current Working Directory."""
    for l in csv_lines:
        print l.encode('utf-8')
    #with open('./export.csv', 'w') as output_file:
        #if INCLUDE_YEARS_SINCE_CREATED:
        #    output_file.write("email,name,years since created,community,role,is member\n")
        #else:
        #    output_file.write("email,name,community,role,is member\n")
        #output_file.writelines(csv_lines)


if __name__ == "__main__":
    main()
