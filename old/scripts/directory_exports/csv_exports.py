#!/usr/bin/env python
"""Utiltiy Functions for Creating CSV Exports."""
import csv


def export_dictionaries(file_name, data):
    """Export data to an Excel CSV File with Headings."""
    keys = list(set(key for datum in data for key in datum.keys()))
    keys.sort()
    with open(file_name, 'w', encoding='utf-8') as output_file:
        output_file.write(u"\ufeff")
        dict_writer = csv.DictWriter(output_file, keys)
        dict_writer.writeheader()
        dict_writer.writerows(data)
