module Directory.Model exposing (Model, initial, paginationConfig)

import Date exposing (Date)
import Directory.Commands exposing (getCommunity, getCommunities, CommunitiesRequestData)
import Directory.Communities exposing (CommunityListing, CommunityDetails)
import Directory.Pagination as Pagination exposing (Pagination)
import Directory.Routing as Routing exposing (Route(..), FilterParam(..))
import Directory.Messages exposing (Msg(CommunityPagination))
import RemoteData exposing (WebData)


{-| Contains the State Used Throughout The Application
-}
type alias Model =
    { communities : Pagination CommunityListing CommunitiesRequestData
    , community : WebData CommunityDetails
    , searchString : String
    , currentDate : Maybe Date
    , route : Route
    }


initial : Route -> ( Model, Cmd Msg )
initial route =
    let
        ( communitiesPagination, paginationCmd, searchString ) =
            listingsInitial route

        ( community, detailsCmd ) =
            detailsInitial route
    in
        ( { communities = communitiesPagination
          , community = community
          , searchString = searchString
          , currentDate = Nothing
          , route = route
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


detailsInitial : Route -> ( WebData CommunityDetails, Cmd Msg )
detailsInitial route =
    case route of
        DetailsRoute slug ->
            ( RemoteData.NotAsked, getCommunity slug )

        ListingsRoute _ ->
            ( RemoteData.NotAsked, Cmd.none )
