module Routing exposing (Route(..), FilterParam(..), inlineFilters, reverse, routeParser)

import Navigation
import UrlParser exposing (Parser, (</>), (<?>), s, int, map, oneOf, parsePath)


type FilterParam
    = VisitorsFilter
    | MembersFilter
    | EstablishedFilter
    | FormingFilter
    | FICMemberFilter


inlineFilters : List FilterParam
inlineFilters =
    [ VisitorsFilter, MembersFilter, EstablishedFilter, FormingFilter, FICMemberFilter ]


filterParamToQueryString : FilterParam -> String
filterParamToQueryString filter =
    case filter of
        VisitorsFilter ->
            "visitors"

        MembersFilter ->
            "members"

        EstablishedFilter ->
            "established"

        FormingFilter ->
            "forming"

        FICMemberFilter ->
            "ficMember"


parseFilterParam : String -> Maybe FilterParam
parseFilterParam str =
    case str of
        "visitors" ->
            Just VisitorsFilter

        "members" ->
            Just MembersFilter

        "established" ->
            Just EstablishedFilter

        "forming" ->
            Just FormingFilter

        "ficMember" ->
            Just FICMemberFilter

        _ ->
            Nothing


filterParams : UrlParser.QueryParser (List FilterParam -> b) b
filterParams =
    UrlParser.customParam "filters"
        (\x ->
            case x of
                Just str ->
                    String.split "," str |> List.filterMap parseFilterParam

                Nothing ->
                    []
        )


filtersToQueryString : List FilterParam -> String
filtersToQueryString =
    List.map filterParamToQueryString
        >> String.join ","
        >> (\s ->
                if not (String.isEmpty s) then
                    "?filters=" ++ s
                else
                    ""
           )


type Route
    = Listings Int (List FilterParam)


parser : Parser (Route -> a) a
parser =
    oneOf
        [ map (Listings 1) (s "directory" </> s "listings" <?> filterParams)
        , map Listings (s "directory" </> s "listings" </> int <?> filterParams)
        ]


reverse : Route -> String
reverse route =
    case route of
        Listings 1 filterParams ->
            "/directory/listings/" ++ filtersToQueryString filterParams

        Listings page filterParams ->
            "/directory/listings/" ++ toString page ++ "/" ++ filtersToQueryString filterParams


routeParser : Navigation.Location -> Route
routeParser =
    parsePath parser >> Maybe.withDefault (Listings 1 [])
