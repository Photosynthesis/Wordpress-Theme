module Routing exposing (..)

import Navigation
import UrlParser exposing (Parser, (</>), (<?>), s, int, map, oneOf, parsePath)


-- QueryString Filters


type FilterParam
    = VisitorsFilter
    | MembersFilter
    | EstablishedFilter
    | FormingFilter
    | FICMemberFilter
    | CommunesFilter
    | EcovillagesFilter
    | CohousingFilter
    | CoopFilter
    | ReligiousFilter
    | JewishFilter
    | ChristianFilter


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

        CommunesFilter ->
            "communes"

        EcovillagesFilter ->
            "ecovillages"

        CohousingFilter ->
            "cohousing"

        CoopFilter ->
            "coops"

        ReligiousFilter ->
            "religious"

        JewishFilter ->
            "jewish"

        ChristianFilter ->
            "christian"


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

        "communes" ->
            Just CommunesFilter

        "ecovillages" ->
            Just EcovillagesFilter

        "cohousing" ->
            Just CohousingFilter

        "coops" ->
            Just CoopFilter

        "religious" ->
            Just ReligiousFilter

        "jewish" ->
            Just JewishFilter

        "christian" ->
            Just ChristianFilter

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



-- Routes


{-| TODO: Expirement w/ splitting Comm Type into different Type & having a single route.
-}
type Route
    = Listings Int (List FilterParam)
    | Communes Int (List FilterParam)
    | Ecovillages Int (List FilterParam)
    | CohousingCommunities Int (List FilterParam)
    | Coops Int (List FilterParam)
    | JewishCommunities Int (List FilterParam)
    | ChristianCommunities Int (List FilterParam)
    | RecentlyUpdated Int (List FilterParam)
    | RecentlyAdded Int (List FilterParam)


getPageTitle : Route -> String
getPageTitle route =
    case route of
        Listings _ _ ->
            "Listings"

        Communes _ _ ->
            "Communes"

        Ecovillages _ _ ->
            "Ecovillages"

        CohousingCommunities _ _ ->
            "Cohousing Communities"

        Coops _ _ ->
            "Co-ops"

        JewishCommunities _ _ ->
            "Jewish Communities"

        ChristianCommunities _ _ ->
            "Christian Communities"

        RecentlyUpdated _ _ ->
            "Recently Updated Communities"

        RecentlyAdded _ _ ->
            "Newest Communities"


{-| Get filters that aren't inherent to the Route. For example, the `Communes`
route will never return a `CommunesFilter`.
-}
getAdditionalFilters : Route -> List FilterParam
getAdditionalFilters route =
    case route of
        Listings _ filters ->
            filters

        Communes _ filters ->
            filters

        Ecovillages _ filters ->
            filters

        CohousingCommunities _ filters ->
            filters

        Coops _ filters ->
            filters

        JewishCommunities _ filters ->
            filters

        ChristianCommunities _ filters ->
            filters

        RecentlyUpdated _ filters ->
            filters

        RecentlyAdded _ filters ->
            filters


{-| Get the filters that are inherent to a Route, ignoring any additional ones.
For example, the `Communes` route will always return `[ CommunesFilter ]`.
-}
getInherentFilters : Route -> List FilterParam
getInherentFilters route =
    case route of
        Listings _ filters ->
            []

        Communes _ _ ->
            [ CommunesFilter ]

        Ecovillages _ _ ->
            [ EcovillagesFilter ]

        CohousingCommunities _ _ ->
            [ CohousingFilter ]

        Coops _ _ ->
            [ CoopFilter ]

        JewishCommunities _ _ ->
            [ ReligiousFilter, JewishFilter ]

        ChristianCommunities _ _ ->
            [ ReligiousFilter, ChristianFilter ]

        RecentlyUpdated _ _ ->
            []

        RecentlyAdded _ _ ->
            []


getFilters : Route -> List FilterParam
getFilters route =
    getAdditionalFilters route ++ getInherentFilters route


getPageAndFilters : Route -> ( Int, List FilterParam )
getPageAndFilters route =
    flip (,) (getFilters route) <|
        case route of
            Listings page _ ->
                page

            Communes page _ ->
                page

            Ecovillages page _ ->
                page

            CohousingCommunities page _ ->
                page

            Coops page _ ->
                page

            JewishCommunities page _ ->
                page

            ChristianCommunities page _ ->
                page

            RecentlyUpdated page _ ->
                page

            RecentlyAdded page _ ->
                page


toPageOne : Route -> (List FilterParam -> Route)
toPageOne route =
    case route of
        Listings _ _ ->
            Listings 1

        Communes _ _ ->
            Communes 1

        Ecovillages _ _ ->
            Ecovillages 1

        CohousingCommunities _ _ ->
            CohousingCommunities 1

        Coops _ _ ->
            Coops 1

        JewishCommunities _ _ ->
            JewishCommunities 1

        ChristianCommunities _ _ ->
            ChristianCommunities 1

        RecentlyUpdated _ _ ->
            RecentlyUpdated 1

        RecentlyAdded _ _ ->
            RecentlyAdded 1


mapPage : (Int -> Int) -> Route -> Route
mapPage func route =
    case route of
        Listings page filters ->
            Listings (func page) filters

        Communes page filters ->
            Communes (func page) filters

        Ecovillages page filters ->
            Ecovillages (func page) filters

        CohousingCommunities page filters ->
            CohousingCommunities (func page) filters

        Coops page filters ->
            Coops (func page) filters

        JewishCommunities page filters ->
            JewishCommunities (func page) filters

        ChristianCommunities page filters ->
            ChristianCommunities (func page) filters

        RecentlyUpdated page filters ->
            RecentlyUpdated (func page) filters

        RecentlyAdded page filters ->
            RecentlyAdded (func page) filters


type Ordering
    = UpdatedDate
    | CreatedDate


getOrdering : Route -> Maybe Ordering
getOrdering route =
    case route of
        RecentlyUpdated _ _ ->
            Just UpdatedDate

        RecentlyAdded _ _ ->
            Just CreatedDate

        _ ->
            Nothing


parser : Parser (Route -> a) a
parser =
    oneOf
        [ map (Listings 1) (s "directory" </> s "listings" <?> filterParams)
        , map Listings (s "directory" </> s "listings" </> int <?> filterParams)
        , map (Communes 1) (s "directory" </> s "communes" <?> filterParams)
        , map Communes (s "directory" </> s "communes" </> int <?> filterParams)
        , map (Ecovillages 1) (s "directory" </> s "ecovillages" <?> filterParams)
        , map Ecovillages (s "directory" </> s "ecovillages" </> int <?> filterParams)
        , map (CohousingCommunities 1) (s "directory" </> s "cohousing-communities" <?> filterParams)
        , map CohousingCommunities (s "directory" </> s "cohousing-communities" </> int <?> filterParams)
        , map (Coops 1) (s "directory" </> s "co-ops" <?> filterParams)
        , map Coops (s "directory" </> s "co-ops" </> int <?> filterParams)
        , map (JewishCommunities 1) (s "directory" </> s "jewish-communities" <?> filterParams)
        , map JewishCommunities (s "directory" </> s "jewish-communities" </> int <?> filterParams)
        , map (ChristianCommunities 1) (s "directory" </> s "christian-communities" <?> filterParams)
        , map ChristianCommunities (s "directory" </> s "christian-communities" </> int <?> filterParams)
        , map (RecentlyUpdated 1) (s "directory" </> s "recently-updated" <?> filterParams)
        , map RecentlyUpdated (s "directory" </> s "recently-updated" </> int <?> filterParams)
        , map (RecentlyAdded 1) (s "directory" </> s "newest-communities" <?> filterParams)
        , map RecentlyAdded (s "directory" </> s "newest-communities" </> int <?> filterParams)
        ]


reverse : Route -> String
reverse route =
    "/directory/"
        ++ case route of
            Listings 1 filterParams ->
                "listings/" ++ filtersToQueryString filterParams

            Listings page filterParams ->
                "listings/" ++ toString page ++ "/" ++ filtersToQueryString filterParams

            Communes 1 filterParams ->
                "communes/" ++ filtersToQueryString filterParams

            Communes page filterParams ->
                "communes/" ++ toString page ++ "/" ++ filtersToQueryString filterParams

            Ecovillages 1 filterParams ->
                "ecovillages/" ++ filtersToQueryString filterParams

            Ecovillages page filterParams ->
                "ecovillages/" ++ toString page ++ "/" ++ filtersToQueryString filterParams

            CohousingCommunities 1 filterParams ->
                "cohousing-communities/" ++ filtersToQueryString filterParams

            CohousingCommunities page filterParams ->
                "cohousing-communities/" ++ toString int ++ "/" ++ filtersToQueryString filterParams

            Coops 1 filterParams ->
                "co-ops/" ++ filtersToQueryString filterParams

            Coops page filterParams ->
                "co-ops/" ++ toString int ++ "/" ++ filtersToQueryString filterParams

            JewishCommunities 1 filterParams ->
                "jewish-communities/" ++ filtersToQueryString filterParams

            JewishCommunities page filterParams ->
                "jewish-communities/" ++ toString page ++ "/" ++ filtersToQueryString filterParams

            ChristianCommunities 1 filterParams ->
                "christian-communities/" ++ filtersToQueryString filterParams

            ChristianCommunities page filterParams ->
                "christian-communities/" ++ toString page ++ "/" ++ filtersToQueryString filterParams

            RecentlyUpdated 1 filterParams ->
                "recently-updated/" ++ filtersToQueryString filterParams

            RecentlyUpdated page filterParams ->
                "recently-updated/" ++ toString page ++ "/" ++ filtersToQueryString filterParams

            RecentlyAdded 1 filterParams ->
                "newest-communities/" ++ filtersToQueryString filterParams

            RecentlyAdded page filterParams ->
                "newest-communities/" ++ toString page ++ "/" ++ filtersToQueryString filterParams


routeParser : Navigation.Location -> Route
routeParser =
    parsePath parser >> Maybe.withDefault (Listings 1 [])
