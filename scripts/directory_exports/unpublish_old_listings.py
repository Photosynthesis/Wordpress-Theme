#!/usr/bin/env python
"""Marks Communities Updated More Than 2 Years Ago as Drafts."""
import datetime

import csv_exports
import db


def main():
    """Read, Filter, & Update the Old Listings, exporting data for US CoHo."""
    cursor = db.get_cursor()
    communities = db.get_communities(cursor)
    cutoff_date = datetime.datetime(2016, 12, 5)
    communities = [
        db.add_community_metas(cursor, community) for community in communities
        if cutoff_date - community.get('updated_at', cutoff_date) >
        datetime.timedelta(days=365.5*2)
        and community.get('post_status', '') == 'publish'
    ]
    us_coho_communities = [
        community for community in communities
        if community.get('country', None) == 'United States'
        and 'Cohousing' in community.get('community_types', '')
    ]
    csv_exports.export_dictionaries(
        'unpublished_coho_communities.csv',
        [item_to_export(c) for c in us_coho_communities])
    csv_exports.export_dictionaries(
        'unpublished_communities.csv',
        [item_to_export(c) for c in communities])

    _ = [db.unpublish_community(cursor, community)
         for community in communities]


def item_to_export(c):
    return {'community': db.get_community_name(c),
            'contact name': c.get('contact_names_public', ''),
            'contact email': c.get('contact_email_public', ''),
            'phone number': c.get('contact_phone_public', ''),
            'private phone': c.get('contact_phone_private', ''),
            'alt email': c.get('alternative_email_not_public,_just_for_backup', ''),
            'state': c.get('state', ''),
            'url': db.get_community_detail_link(c),
            'website': c.get('website_address_url_--_must_start_with_http://', ''),
            'twitter': c.get('twitter_profile_url'),
            'facebook': c.get('facebook_profile_url'),
            'created': c.get('created_at', ''),
            'updated': c.get('updated_at', '')
            }


if __name__ == '__main__':
    main()
