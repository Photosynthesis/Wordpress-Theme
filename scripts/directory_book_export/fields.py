def make_field(number, label, tag):
    return {"number": number, "label": label, "tag": tag}


NAME = make_field(9, '', 'Community name')
MISSION = make_field(286, '', 'Mission statement')
DESCRIPTION = make_field(277, '', 'Community description')
TYPE = make_field(262, 'Type:', 'Community type')
LOCATION = make_field(952, 'Location:', 'Community Locaton')
YEAR_FOUNDED = make_field(0, 'Began:', 'Year Founded')
DECISION_METHOD = make_field(
    250, 'Decision-Making:',
    'Which decision-making method does your Community primarily use?')
INCOME = make_field(
    241, 'Income Sharing:',
    'To what extent do members of your community share income?')
LABOR_HOURS = make_field(
    245, 'Work Hours/wk:', 'How many hours of work are required weekly?')
NEW_MEMBERS = make_field(
    257, 'Accepting Members:', 'Is your community open to new members?')
NEW_VISITORS = make_field(
    256, 'Accepting Visitors:', 'Is your community open to visitors?')
ADULTS = make_field(
    254, 'Adults:', 'How many Adults are in your community?')
CHILDREN = make_field(
    420, 'Children:', 'How many children are in your community?')
FOOD = make_field(
    294, 'Food Grown:',
    'What % of your food does your community currently produce?')
ENERGY = make_field(
    299, 'Renewable Energy Produced:',
    'What % of energy does your community currently source from renewables?')
DIET = make_field(236, 'Diet:', 'Diet')
ADDRESS1 = make_field(425, '', 'street address line 1')
ADDRESS2 = make_field(426, '', 'street address line 2')
CITY = make_field(427, '', 'city')
STATE = make_field(428, '', 'state or province')
POSTAL_CODE = make_field(429, '', 'postal code')
EMAIL = make_field(199, '', 'contact email')
PHONE = make_field(201, '', 'contact phone')
WEBSITE = make_field(227, '', 'website address')
LISTING_LINK = make_field(
    'listing link', 'Full Listing:',
    'website address to full listing on ic.org')
