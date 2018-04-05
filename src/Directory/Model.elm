module Directory.Model exposing (Model, initial, paginationConfig)

import Date exposing (Date)
import Directory.Commands exposing (getCommunities, CommunitiesRequestData)
import Directory.Communities exposing (Community)
import Directory.Pagination as Pagination exposing (Pagination)
import Directory.Routing as Routing exposing (Route(..), FilterParam(..))


{-| Contains the State Used Throughout The Application
-}
type alias Model =
    { communities : Pagination Community CommunitiesRequestData
    , searchString : String
    , currentDate : Maybe Date
    , route : Route
    }


initial : Route -> ( Model, Cmd (Pagination.Msg Community) )
initial route =
    let
        ( page, filters ) =
            Routing.getPageAndFilters route

        requestData =
            CommunitiesRequestData filters (Routing.getOrdering route)

        ( communitiesPagination, paginationCmd ) =
            Pagination.initial paginationConfig requestData page

        searchString =
            Routing.getSearchFilter filters |> Maybe.withDefault ""
    in
        ( { communities = communitiesPagination
          , searchString = searchString
          , currentDate = Nothing
          , route = route
          }
        , paginationCmd
        )


paginationConfig : Pagination.Config Community CommunitiesRequestData
paginationConfig =
    Pagination.makeConfig getCommunities
