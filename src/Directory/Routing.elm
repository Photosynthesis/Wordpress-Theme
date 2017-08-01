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
    | SearchFilter String


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

        SearchFilter str ->
            "search=" ++ str


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


searchParam : UrlParser.QueryParser (Maybe FilterParam -> b) b
searchParam =
    UrlParser.customParam "search"
        (Maybe.map SearchFilter)


addQueryParams : a -> Parser a (List FilterParam -> b) -> Parser (b -> c) c
addQueryParams route pathParser =
    let
        applySearch filters maybeSearch =
            case maybeSearch of
                Just filter ->
                    filter :: filters

                Nothing ->
                    filters
    in
        map route (pathParser </> map applySearch (UrlParser.top <?> filterParams <?> searchParam))


getSearchFilter : List FilterParam -> Maybe String
getSearchFilter fs =
    case fs of
        [] ->
            Nothing

        (SearchFilter str) :: _ ->
            Just str

        _ :: xs ->
            getSearchFilter xs


filtersToQueryString : List FilterParam -> String
filtersToQueryString filters =
    let
        ( searchFilters, otherFilters ) =
            List.partition
                (\f ->
                    case f of
                        SearchFilter _ ->
                            True

                        _ ->
                            False
                )
                filters

        filterString =
            otherFilters
                |> List.map filterParamToQueryString
                |> String.join ","
                |> (\s ->
                        if not (String.isEmpty s) then
                            "filters=" ++ s
                        else
                            ""
                   )

        searchString =
            case List.head searchFilters of
                Just (SearchFilter "") ->
                    ""

                Just (SearchFilter str) ->
                    "search=" ++ str

                _ ->
                    ""
    in
        List.filter (not << String.isEmpty) [ filterString, searchString ]
            |> String.join "&"
            |> (\s ->
                    if String.isEmpty s then
                        ""
                    else
                        "?" ++ s
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


mapBoth : (Int -> Int) -> (List FilterParam -> List FilterParam) -> Route -> Route
mapBoth func1 func2 route =
    case route of
        Listings page filters ->
            Listings (func1 page) (func2 filters)

        Communes page filters ->
            Communes (func1 page) (func2 filters)

        Ecovillages page filters ->
            Ecovillages (func1 page) (func2 filters)

        CohousingCommunities page filters ->
            CohousingCommunities (func1 page) (func2 filters)

        Coops page filters ->
            Coops (func1 page) (func2 filters)

        JewishCommunities page filters ->
            JewishCommunities (func1 page) (func2 filters)

        ChristianCommunities page filters ->
            ChristianCommunities (func1 page) (func2 filters)

        RecentlyUpdated page filters ->
            RecentlyUpdated (func1 page) (func2 filters)

        RecentlyAdded page filters ->
            RecentlyAdded (func1 page) (func2 filters)


mapPage : (Int -> Int) -> Route -> Route
mapPage func =
    mapBoth func identity


mapFilters : (List FilterParam -> List FilterParam) -> Route -> Route
mapFilters =
    mapBoth identity


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
        [ addQueryParams (Listings 1) (s "directory" </> s "listings")
        , addQueryParams Listings (s "directory" </> s "listings" </> int)
        , addQueryParams (Communes 1) (s "directory" </> s "communes")
        , addQueryParams Communes (s "directory" </> s "communes" </> int)
        , addQueryParams (Ecovillages 1) (s "directory" </> s "ecovillages")
        , addQueryParams Ecovillages (s "directory" </> s "ecovillages" </> int)
        , addQueryParams (CohousingCommunities 1) (s "directory" </> s "cohousing-communities")
        , addQueryParams CohousingCommunities (s "directory" </> s "cohousing-communities" </> int)
        , addQueryParams (Coops 1) (s "directory" </> s "co-ops")
        , addQueryParams Coops (s "directory" </> s "co-ops" </> int)
        , addQueryParams (JewishCommunities 1) (s "directory" </> s "jewish-communities")
        , addQueryParams JewishCommunities (s "directory" </> s "jewish-communities" </> int)
        , addQueryParams (ChristianCommunities 1) (s "directory" </> s "christian-communities")
        , addQueryParams ChristianCommunities (s "directory" </> s "christian-communities" </> int)
        , addQueryParams (RecentlyUpdated 1) (s "directory" </> s "recently-updated")
        , addQueryParams RecentlyUpdated (s "directory" </> s "recently-updated" </> int)
        , addQueryParams (RecentlyAdded 1) (s "directory" </> s "newest-communities")
        , addQueryParams RecentlyAdded (s "directory" </> s "newest-communities" </> int)
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
