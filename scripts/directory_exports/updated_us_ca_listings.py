#!/usr/bin/env python
"""Export US/Canadian Communities Updated in the Last 2 Years."""
import datetime

from csv_exports import export_dictionaries
from db import get_cursor, get_communities, get_community_name


def main():
    cursor = get_cursor()
    communities = get_communities(cursor)
    today = datetime.datetime.now()
    communities = [
        community for community in communities
        if today - community.get('updated_at', today) <
        datetime.timedelta(days=365.5*2)
        and community.get('country', '') in ['United States', 'Canada']
    ]
    data = [
        {'community': get_community_name(c),
         'contact name': c.get('contact_names_public', ''),
         'contact email': c.get('contact_email_public', ''),
         'phone number': c.get('contact_phone_public', ''),
         'country': c.get('country', ''),
         'state': c.get('state', ''),
         'province': c.get('state/province', ''),
         'city': c.get('city/town/village', ''),
         'zip': c.get('postal_code', ''),
         'street': c.get('street_address_line_1', ''),
         'street2': c.get('street_address_line_2', '')}
        for c in communities
    ]
    export_dictionaries('us_ca_updated_listings.csv', data)


if __name__ == '__main__':
    main()
