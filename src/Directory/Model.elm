module Directory.Model exposing (Model, initial, paginationConfig)

import Date exposing (Date)
import Directory.Commands exposing (WPNonce, getCommunity, getCommunities, CommunitiesRequestData)
import Directory.Communities exposing (CommunityListing, CommunityDetails, ImageData)
import Directory.Pagination as Pagination exposing (Pagination)
import Directory.Routing as Routing exposing (Route(..), FilterParam(..))
import Directory.Messages exposing (Msg(CommunityPagination))
import Gallery
import RemoteData exposing (WebData)


{-| Contains the State Used Throughout The Application
-}
type alias Model =
    { communities : Pagination CommunityListing CommunitiesRequestData
    , community : WebData CommunityDetails
    , communityGallery : Gallery.Model ImageData
    , searchString : String
    , currentDate : Maybe Date
    , route : Route
    , wpNonce : WPNonce
    }


initial : Route -> WPNonce -> ( Model, Cmd Msg )
initial route nonce =
    let
        ( communitiesPagination, paginationCmd, searchString ) =
            listingsInitial route

        ( community, detailsCmd ) =
            detailsInitial nonce route
    in
        ( { communities = communitiesPagination
          , community = community
          , communityGallery = Gallery.initial
          , searchString = searchString
          , currentDate = Nothing
          , route = route
          , wpNonce = nonce
          }
        , Cmd.batch [ Cmd.map CommunityPagination paginationCmd, detailsCmd ]
        )


listingsInitial : Route -> ( Pagination CommunityListing CommunitiesRequestData, Cmd (Pagination.Msg CommunityListing), String )
listingsInitial route =
    let
        ( page, filters, ordering ) =
            case route of
                ListingsRoute listings ->
                    Routing.getPageAndFilters listings
                        |> \( p, fs ) -> ( p, fs, Routing.getOrdering listings )

                DetailsRoute _ ->
                    ( 1, [], Nothing )

        requestData =
            CommunitiesRequestData filters ordering

        ( communitiesPagination, paginationCmd ) =
            Pagination.initial paginationConfig requestData page

        searchString =
            Routing.getSearchFilter filters |> Maybe.withDefault ""
    in
        case route of
            ListingsRoute _ ->
                ( communitiesPagination, paginationCmd, searchString )

            DetailsRoute _ ->
                ( communitiesPagination, Cmd.none, "" )


paginationConfig : Pagination.Config CommunityListing CommunitiesRequestData
paginationConfig =
    Pagination.makeConfig getCommunities


detailsInitial : WPNonce -> Route -> ( WebData CommunityDetails, Cmd Msg )
detailsInitial wpNonce route =
    case route of
        DetailsRoute slug ->
            ( RemoteData.Loading, getCommunity wpNonce slug )

        ListingsRoute _ ->
            ( RemoteData.NotAsked, Cmd.none )
