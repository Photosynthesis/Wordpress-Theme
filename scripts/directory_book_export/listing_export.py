#!/usr/bin/env python3
from core import Exporter
import fields as F

LISTING_FIELDS = [
    F.NAME,
    F.MISSION,
    F.DESCRIPTION,
    F.TYPE,
    F.DECISION_METHOD,
    F.INCOME,
    F.LABOR_HOURS,
    F.ADULTS,
    F.CHILDREN,
    F.LOCATION,
    F.FOOD,
    F.ENERGY,
    F.ADDRESS1,
    F.ADDRESS2,
    F.CITY,
    F.STATE,
    F.POSTAL_CODE,
    F.EMAIL,
    F.PHONE,
    F.WEBSITE,
    F.NEW_MEMBERS,
    F.NEW_VISITORS,
]


def main():
    exporter = Exporter()
    exporter.create_export(LISTING_FIELDS, 'listings.txt')


if __name__ == '__main__':
    main()
