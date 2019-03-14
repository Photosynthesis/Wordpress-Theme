module Directory.Routing exposing (FilterParam(..), ListingsRoute(..), Ordering(..), Route(..), addQueryParams, filterParamToQueryString, filterParams, filtersToQueryString, getAdditionalFilters, getFilters, getInherentFilters, getOrdering, getPageAndFilters, getPageTitle, getSearchFilter, inlineFilters, listingsParser, listingsReverse, mapBoth, mapFilters, mapPage, parseFilterParam, parser, reverse, routeParser, toPageOne)

{-| Contains Types & Functions Related to the Application's Internal Routing.
-}

import Navigation
import UrlParser exposing ((</>), (<?>), Parser, int, map, oneOf, parsePath, s, string)



-- QueryString Filters


{-| The Filters Available Through GET Parameters.
-}
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
    | SharedHousingFilter
    | StudentHousingFilter
    | ReligiousFilter
    | JewishFilter
    | ChristianFilter
    | CountryFilter String
    | StateFilter String
    | ProvinceFilter String
    | SearchFilter String


{-| The Filters to Display on Listings Pages.
-}
inlineFilters : List FilterParam
inlineFilters =
    [ VisitorsFilter, MembersFilter, EstablishedFilter, FormingFilter, FICMemberFilter ]


{-| Return the Value of a `FilterParam` to be Used in a GET Parameter.
-}
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

        SharedHousingFilter ->
            "sharedHousing"

        StudentHousingFilter ->
            "studentHousing"

        ReligiousFilter ->
            "religious"

        JewishFilter ->
            "jewish"

        ChristianFilter ->
            "christian"

        CountryFilter str ->
            str

        StateFilter str ->
            str

        ProvinceFilter str ->
            str

        SearchFilter str ->
            str


{-| Attempt to Parse a String Into a `FilterParam`.
-}
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

        "sharedHousing" ->
            Just SharedHousingFilter

        "studentHousing" ->
            Just SharedHousingFilter

        "religious" ->
            Just ReligiousFilter

        "jewish" ->
            Just JewishFilter

        "christian" ->
            Just ChristianFilter

        _ ->
            Nothing


{-| Parse a `FilterParam` List From the QueryString
-}
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


{-| Parse a `SearchFilter` And/Or `FilterParam` List From the QueryString,
Returning Them as a Single List.
-}
addQueryParams : a -> Parser a (List FilterParam -> b) -> Parser (b -> c) c
addQueryParams route pathParser =
    let
        addFilterIfExists filters maybeSearch =
            case maybeSearch of
                Just filter ->
                    filter :: filters

                Nothing ->
                    filters

        withTopLevelFilter name filter parser =
            map addFilterIfExists
                (parser <?> UrlParser.customParam name (Maybe.map filter))

        queryParser =
            UrlParser.top
                <?> filterParams
                |> withTopLevelFilter "search" SearchFilter
                |> withTopLevelFilter "country" CountryFilter
                |> withTopLevelFilter "state" StateFilter
                |> withTopLevelFilter "province" ProvinceFilter
    in
    map route (pathParser </> queryParser)


{-| Try to Pull a `SearchFilter` Out of a `FilterParam` List.
-}
getSearchFilter : List FilterParam -> Maybe String
getSearchFilter fs =
    case fs of
        [] ->
            Nothing

        (SearchFilter str) :: _ ->
            Just str

        _ :: xs ->
            getSearchFilter xs


{-| Build a QueryString From a `FilterParam` List, With a Separate Option/Value
for a `SearchFilter`.
-}
filtersToQueryString : List FilterParam -> String
filtersToQueryString filters =
    let
        isSearch filter =
            case filter of
                SearchFilter _ ->
                    True

                _ ->
                    False

        isCountry filter =
            case filter of
                CountryFilter _ ->
                    True

                _ ->
                    False

        isState filter =
            case filter of
                StateFilter _ ->
                    True

                _ ->
                    False

        isProvince filter =
            case filter of
                ProvinceFilter _ ->
                    True

                _ ->
                    False

        ( topLevelFilterString, otherFilters ) =
            List.foldl
                (\( topLevelFilter, topLevelParameterName ) ( topLevelFilterString_, otherFilters_ ) ->
                    List.partition topLevelFilter otherFilters_
                        |> Tuple.mapFirst
                            (List.map (makeTopLevelParameter topLevelParameterName)
                                >> (::) topLevelFilterString_
                                >> String.join "&"
                            )
                )
                ( "", filters )
                [ ( isSearch, "search" )
                , ( isCountry, "country" )
                , ( isState, "state" )
                , ( isProvince, "province" )
                ]

        makeTopLevelParameter parameterName filter =
            parameterName ++ "=" ++ filterParamToQueryString filter

        filterString =
            otherFilters
                |> List.sortBy toString
                |> List.map filterParamToQueryString
                |> String.join ","
                |> (\str ->
                        if not (String.isEmpty str) then
                            "filters=" ++ str

                        else
                            ""
                   )
    in
    List.filter (not << String.isEmpty) [ filterString, topLevelFilterString ]
        |> String.join "&"
        |> (\str ->
                if String.isEmpty str then
                    ""

                else
                    "?" ++ str
           )



-- Routes


type Route
    = ListingsRoute ListingsRoute
    | DetailsRoute String


{-| The Potential Listing Routes, With a Page Number & List of Filters.

TODO: Experiment w/ splitting Comm Type into different Type & having a single route.
Or refactor parameters into single record type w/ `getParameters : Route -> ...`

-}
type ListingsRoute
    = Listings Int (List FilterParam)
    | Communes Int (List FilterParam)
    | Ecovillages Int (List FilterParam)
    | CohousingCommunities Int (List FilterParam)
    | Coops Int (List FilterParam)
    | SharedHousing Int (List FilterParam)
    | StudentHousing Int (List FilterParam)
    | ReligiousCommunities Int (List FilterParam)
    | JewishCommunities Int (List FilterParam)
    | ChristianCommunities Int (List FilterParam)
    | RecentlyUpdated Int (List FilterParam)
    | RecentlyAdded Int (List FilterParam)


{-| Return the Page Title for a Route.
-}
getPageTitle : ListingsRoute -> String
getPageTitle route =
    case route of
        Listings _ _ ->
            "Community Directory"

        Communes _ _ ->
            "Communes"

        Ecovillages _ _ ->
            "Ecovillages"

        CohousingCommunities _ _ ->
            "Cohousing Communities"

        Coops _ _ ->
            "Co-ops"

        SharedHousing _ _ ->
            "Shared Housing, Cohouseholding, & Coliving Communities"

        StudentHousing _ _ ->
            "Student Housing & Student Co-ops"

        ReligiousCommunities _ _ ->
            "Spiritual & Religious Communities"

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
getAdditionalFilters : ListingsRoute -> List FilterParam
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

        StudentHousing _ filters ->
            filters

        SharedHousing _ filters ->
            filters

        ReligiousCommunities _ filters ->
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
getInherentFilters : ListingsRoute -> List FilterParam
getInherentFilters route =
    case route of
        Listings _ _ ->
            []

        Communes _ _ ->
            [ CommunesFilter ]

        Ecovillages _ _ ->
            [ EcovillagesFilter ]

        CohousingCommunities _ _ ->
            [ CohousingFilter ]

        Coops _ _ ->
            [ CoopFilter ]

        StudentHousing _ _ ->
            [ StudentHousingFilter ]

        SharedHousing _ _ ->
            [ SharedHousingFilter ]

        ReligiousCommunities _ _ ->
            [ ReligiousFilter ]

        JewishCommunities _ _ ->
            [ ReligiousFilter, JewishFilter ]

        ChristianCommunities _ _ ->
            [ ReligiousFilter, ChristianFilter ]

        RecentlyUpdated _ _ ->
            []

        RecentlyAdded _ _ ->
            []


{-| Get Both the Inherent & Additional Filters for a Route.
-}
getFilters : ListingsRoute -> List FilterParam
getFilters route =
    getAdditionalFilters route ++ getInherentFilters route


{-| Return the Page Number & All Filters for a Route.
-}
getPageAndFilters : ListingsRoute -> ( Int, List FilterParam )
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

            SharedHousing page _ ->
                page

            StudentHousing page _ ->
                page

            ReligiousCommunities page _ ->
                page

            JewishCommunities page _ ->
                page

            ChristianCommunities page _ ->
                page

            RecentlyUpdated page _ ->
                page

            RecentlyAdded page _ ->
                page


{-| Return a Function that Will Return the First Page of a Route When Given a
`FilterParam` List.
-}
toPageOne : ListingsRoute -> (List FilterParam -> ListingsRoute)
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

        SharedHousing _ _ ->
            SharedHousing 1

        StudentHousing _ _ ->
            StudentHousing 1

        ReligiousCommunities _ _ ->
            ReligiousCommunities 1

        JewishCommunities _ _ ->
            JewishCommunities 1

        ChristianCommunities _ _ ->
            ChristianCommunities 1

        RecentlyUpdated _ _ ->
            RecentlyUpdated 1

        RecentlyAdded _ _ ->
            RecentlyAdded 1


{-| Map Transformations to Both the Page & `FilterParam` List of a Route.
-}
mapBoth : (Int -> Int) -> (List FilterParam -> List FilterParam) -> ListingsRoute -> ListingsRoute
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

        SharedHousing page filters ->
            SharedHousing (func1 page) (func2 filters)

        StudentHousing page filters ->
            StudentHousing (func1 page) (func2 filters)

        ReligiousCommunities page filters ->
            ReligiousCommunities (func1 page) (func2 filters)

        JewishCommunities page filters ->
            JewishCommunities (func1 page) (func2 filters)

        ChristianCommunities page filters ->
            ChristianCommunities (func1 page) (func2 filters)

        RecentlyUpdated page filters ->
            RecentlyUpdated (func1 page) (func2 filters)

        RecentlyAdded page filters ->
            RecentlyAdded (func1 page) (func2 filters)


{-| Map a Transformation to the Page Number of a Route.
-}
mapPage : (Int -> Int) -> ListingsRoute -> ListingsRoute
mapPage func =
    mapBoth func identity


{-| Map a Transformation to the `FilterParam` List of a Route.
-}
mapFilters : (List FilterParam -> List FilterParam) -> ListingsRoute -> ListingsRoute
mapFilters =
    mapBoth identity


{-| An `Ordering` Represents the Inherent Ordering of a Route.
-}
type Ordering
    = UpdatedDate
    | CreatedDate


{-| Return the `Ordering` of a `Route`, for Route's That Have Orderings.
-}
getOrdering : ListingsRoute -> Maybe Ordering
getOrdering route =
    case route of
        RecentlyUpdated _ _ ->
            Just UpdatedDate

        RecentlyAdded _ _ ->
            Just CreatedDate

        _ ->
            Nothing


{-| Return a Parser for all Listings Routes.
-}
listingsParser : Parser (ListingsRoute -> a) a
listingsParser =
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
        , addQueryParams (SharedHousing 1) (s "directory" </> s "shared-housing")
        , addQueryParams SharedHousing (s "directory" </> s "shared-housing" </> int)
        , addQueryParams (StudentHousing 1) (s "directory" </> s "student-housing")
        , addQueryParams StudentHousing (s "directory" </> s "student-housing" </> int)
        , addQueryParams (ReligiousCommunities 1) (s "directory" </> s "spiritual-and-religious")
        , addQueryParams ReligiousCommunities (s "directory" </> s "spiritual-and-religious" </> int)
        , addQueryParams (JewishCommunities 1) (s "directory" </> s "jewish-communities")
        , addQueryParams JewishCommunities (s "directory" </> s "jewish-communities" </> int)
        , addQueryParams (ChristianCommunities 1) (s "directory" </> s "christian-communities")
        , addQueryParams ChristianCommunities (s "directory" </> s "christian-communities" </> int)
        , addQueryParams (RecentlyUpdated 1) (s "directory" </> s "recently-updated")
        , addQueryParams RecentlyUpdated (s "directory" </> s "recently-updated" </> int)
        , addQueryParams (RecentlyAdded 1) (s "directory" </> s "newest-communities")
        , addQueryParams RecentlyAdded (s "directory" </> s "newest-communities" </> int)
        ]


parser : Parser (Route -> a) a
parser =
    oneOf
        [ map ListingsRoute listingsParser
        , map DetailsRoute (s "directory" </> string)
        ]


{-| Return the Path for a `ListingsRoute`.
-}
listingsReverse : ListingsRoute -> String
listingsReverse route =
    "/directory/"
        ++ (case route of
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
                    "cohousing-communities/" ++ toString page ++ "/" ++ filtersToQueryString filterParams

                Coops 1 filterParams ->
                    "co-ops/" ++ filtersToQueryString filterParams

                Coops page filterParams ->
                    "co-ops/" ++ toString page ++ "/" ++ filtersToQueryString filterParams

                SharedHousing 1 filterParams ->
                    "shared-housing/" ++ filtersToQueryString filterParams

                SharedHousing page filterParams ->
                    "shared-housing/" ++ toString page ++ "/" ++ filtersToQueryString filterParams

                StudentHousing 1 filterParams ->
                    "student-housing/" ++ filtersToQueryString filterParams

                StudentHousing page filterParams ->
                    "student-housing/" ++ toString page ++ "/" ++ filtersToQueryString filterParams

                ReligiousCommunities 1 filterParams ->
                    "spiritual-and-religious/" ++ filtersToQueryString filterParams

                ReligiousCommunities page filterParams ->
                    "spiritual-and-religious/" ++ toString page ++ "/" ++ filtersToQueryString filterParams

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
           )


reverse : Route -> String
reverse route =
    case route of
        ListingsRoute listingsRoute ->
            listingsReverse listingsRoute

        DetailsRoute slug ->
            "/directory/" ++ slug ++ "/"


{-| Parse a Path into a Route, Defaulting to the Listings Route.

TODO: Add 404 route & default to it.

-}
routeParser : Navigation.Location -> Route
routeParser =
    parsePath parser >> Maybe.withDefault (ListingsRoute <| Listings 1 [])
