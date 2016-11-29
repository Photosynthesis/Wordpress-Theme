#!/usr/bin/env python
"""Marks Communities Updated More Than 2 Years Ago as Drafts."""
import datetime

import csv_exports
import db


def main():
    """Read, Filter, & Update the Old Listings, exporting data for US CoHo."""
    cursor = db.get_cursor()
    communities = db.get_communities(cursor)
    today = datetime.datetime.now()
    communities = [
        db.add_community_metas(cursor, community) for community in communities
        if today - community.get('updated_at', today) >
        datetime.timedelta(days=365.5*2)
    ]
    us_coho_communities = [
        community for community in communities
        if community.get('country', None) == 'United States'
        and 'Cohousing' in community.get('community_types', '')
    ]
    csv_exports.export_dictionaries(
        'unpublished_coho_communities.csv',
        [{'community': db.get_community_name(c),
          'contact name': c.get('contact_names_public', ''),
          'contact email': c.get('contact_email_public', ''),
          'phone number': c.get('contact_phone_public', ''),
          'state': c.get('state', ''),
          'url': db.get_community_detail_link(c)}
         for c in us_coho_communities]
    )

    _ = [db.unpublish_community(cursor, community)
         for community in communities]


if __name__ == '__main__':
    main()
