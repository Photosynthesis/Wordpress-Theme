module Model exposing (Model, initial, paginationConfig)

import Date exposing (Date)
import Commands exposing (getCommunities)
import Communities exposing (Community)
import Pagination exposing (Pagination)
import Routing exposing (Route(..), FilterParam(..))


type alias Model =
    { communities : Pagination Community FilterParam
    , currentDate : Maybe Date
    , route : Route
    }


initial : Route -> ( Model, Cmd (Pagination.Msg Community) )
initial route =
    let
        ( page, filters ) =
            Routing.getPageAndFilters route

        ( communitiesPagination, paginationCmd ) =
            Pagination.initial paginationConfig filters page
    in
        ( { communities = communitiesPagination
          , currentDate = Nothing
          , route = route
          }
        , paginationCmd
        )


paginationConfig : Pagination.Config Community FilterParam
paginationConfig =
    Pagination.makeConfig getCommunities
