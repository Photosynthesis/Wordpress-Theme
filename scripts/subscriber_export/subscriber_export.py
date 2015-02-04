#!/usr/bin/python2
"""Combine the results of the 2 subscriber_export queries into a final CSV

You should run the two associated subscriber_export_*.sql queries, and save
CSV exports into `orders.csv` and `posts.csv` files located in the same folder
as this script. You can then run this script to generate
`subscriber_export_output.csv`.

"""
import csv


def main():
    """Grab & Combine the Input Files."""
    order_number_to_export_info = {}
    fill_with_order_data(order_number_to_export_info)
    fill_with_post_data(order_number_to_export_info)
    export_to_csv(order_number_to_export_info)


def fill_with_order_data(order_data):
    """Pull the data out of the order CSV file."""
    with open('./orders.csv') as input_csv:
        order_reader = csv.reader(input_csv)
        for row in order_reader:
            meta_key, meta_value, _, order_id = [
                field.strip() for field in row
            ]
            if meta_key == '_subscription_start_date':
                meta_value = meta_value.split()[0]
            if order_id not in order_data:
                order_data[order_id] = {meta_key: meta_value}
            else:
                order_data[order_id][meta_key] = meta_value


def fill_with_post_data(order_data):
    """Only add the information for posts that exist in the order_data."""
    with open('./posts.csv') as input_csv:
        for row in input_csv.readlines():
            row = row.replace('\xef\xbb\xbf', '').replace('"', '')
            order_id, order_date, _, meta_key, meta_value, _ = [
                field.strip() for field in row.split(',')
            ]
            if order_id in order_data:
                order_date = order_date.split()[0]
                order_data[order_id]['order_date'] = order_date
                order_data[order_id][meta_key] = meta_value


def export_to_csv(data):
    """Build a CSV out of the data dictionary."""
    keys = set()
    _ = [keys.add(key) for order in data.values() for key in order.keys()]
    with open('subscriber_export.csv', 'w') as output_file:
        output_file.write(u'\ufeff'.encode('utf8'))  # Required for Excel UTF-8
        dict_writer = csv.DictWriter(output_file, keys)
        dict_writer.writeheader()
        dict_writer.writerows(data.values())


if __name__ == "__main__":
    main()
