module Directory.Commands exposing
    ( CommunitiesRequestData
    , WPNonce(..)
    , getCommunities
    , getCommunity
    , newPage
    , validateCommunity
    )

{-| Contains Commands & Relevant Types Used in the Application.
-}

import Directory.Communities exposing (CommunityID(..), CommunityListing)
import Directory.Decoders as Decoders
import Directory.Messages exposing (Msg(FetchCommunityDetails, ValidateCommunity))
import Directory.Pagination as Pagination
import Directory.Ports as Ports
import Directory.Routing exposing (FilterParam(..), Ordering(..), Route(..), getPageTitle, reverse)
import Http
import Json.Decode as Decode
import Json.Encode as Encode
import Navigation
import RemoteData


{-| Wraps the `wp_rest` nonce passed via flags & used in API requests.
-}
type WPNonce
    = WPNonce String


{-| Fetch the Details of a Single Community
-}
getCommunity : WPNonce -> String -> Cmd Msg
getCommunity (WPNonce wpNonce) slug =
    Http.get (String.join "" [ "/wp-json/v1/directory/entry/", "?slug=", slug, "&_wpnonce=", wpNonce ])
        Decoders.communityDetails
        |> RemoteData.sendRequest
        |> Cmd.map FetchCommunityDetails


{-| Validate a listing & update the Last Verified Date if there are no errors.
-}
validateCommunity : WPNonce -> CommunityID -> Cmd Msg
validateCommunity (WPNonce wpNonce) (CommunityID communityID) =
    Http.request
        { method = "POST"
        , headers = [ Http.header "X-WP-Nonce" wpNonce ]
        , url = "/wp-json/v1/directory/entry/validate/"
        , body =
            Http.jsonBody <| Encode.object [ ( "communityId", Encode.int communityID ) ]
        , expect =
            Http.expectJson <| Decode.field "isValid" Decode.bool
        , timeout = Nothing
        , withCredentials = False
        }
        |> RemoteData.sendRequest
        |> Cmd.map ValidateCommunity


{-| The Data Type Stored by the Pagination & Passed to the Fetch Command.
-}
type alias CommunitiesRequestData =
    { filters : List FilterParam
    , ordering : Maybe Ordering
    }


{-| Fetch A Page of Communities Using The Set Filters & Ordering.
-}
getCommunities : CommunitiesRequestData -> Int -> Http.Request (Pagination.FetchResponse CommunityListing)
getCommunities { filters, ordering } page =
    let
        filterQueryString =
            if not (List.isEmpty filters) then
                "&" ++ String.join "&" (List.map filterToApiQuery filters)

            else
                ""

        orderQueryString =
            if not (String.isEmpty <| orderingToApiQuery ordering) then
                "&" ++ orderingToApiQuery ordering

            else
                ""
    in
    Decode.map2 Pagination.FetchResponse
        (Decode.field "listings" (Decode.list Decoders.communityListing))
        (Decode.field "totalCount" Decode.int)
        |> Http.get
            (String.join ""
                [ "/wp-json/v1/directory/entries/"
                , "?page="
                , toString page
                , filterQueryString
                , orderQueryString
                ]
            )


{-| Return the Commands Relevant For Switching to a New `Route`:

  - Change the URL
  - Scroll to the #main Element
  - Set the Page Title

-}
newPage : Route -> Cmd msg
newPage newRoute =
    let
        pageTitle =
            case newRoute of
                ListingsRoute listings ->
                    getPageTitle listings

                DetailsRoute _ ->
                    "Listing Details"
    in
    Cmd.batch
        [ Navigation.newUrl <| reverse newRoute
        , Ports.scrollTo "main"
        , Ports.setPageTitle <| pageTitle
        ]


{-| Return the Backend API QueryString for a `FilterParam`.
-}
filterToApiQuery : FilterParam -> String
filterToApiQuery filter =
    case filter of
        VisitorsFilter ->
            "visitors[]=Yes"

        MembersFilter ->
            "members[]=Yes"

        EstablishedFilter ->
            "status[]=Established"

        FormingFilter ->
            "status[]=Forming,Re-forming"

        FICMemberFilter ->
            "membership[]=Yes"

        CommunesFilter ->
            "type[]=Commune"

        EcovillagesFilter ->
            "type[]=Ecovillage"

        CohousingFilter ->
            "type[]=Cohousing"

        SharedHousingFilter ->
            "type[]=Shared Housing"

        StudentHousingFilter ->
            "type[]=Student Housing"

        CoopFilter ->
            "description[]=coop"

        ReligiousFilter ->
            "type[]=Spiritual"

        JewishFilter ->
            "spiritual[]=Jewish"

        ChristianFilter ->
            "spiritual[]=Christian"

        CountryFilter str ->
            "country=" ++ str

        StateFilter str ->
            "state=" ++ str

        ProvinceFilter str ->
            "province=" ++ str

        SearchFilter str ->
            "search=" ++ str


{-| Return the Backend API QueryString for a `Ordering` Parameter.
-}
orderingToApiQuery : Maybe Ordering -> String
orderingToApiQuery =
    let
        orderingToQuery ordering =
            case ordering of
                UpdatedDate ->
                    "order=updated"

                CreatedDate ->
                    "order=created"
    in
    Maybe.map orderingToQuery >> Maybe.withDefault ""
