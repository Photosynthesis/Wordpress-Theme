module Routing exposing (Route(..), reverse, routeParser)

import Navigation
import UrlParser exposing (Parser, (</>), s, int, map, oneOf, parsePath)


type Route
    = Listings Int


parser : Parser (Route -> a) a
parser =
    oneOf
        [ map (Listings 1) (s "directory/listings/")
        , map Listings (s "directory" </> s "listings" </> int)
        ]


reverse : Route -> String
reverse route =
    case route of
        Listings 1 ->
            "/directory/listings/"

        Listings page ->
            "/directory/listings/" ++ toString page ++ "/"


routeParser : Navigation.Location -> Route
routeParser =
    parsePath parser >> Maybe.withDefault (Listings 1)
