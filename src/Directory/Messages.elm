module Messages exposing (..)

import Date exposing (Date)
import Communities exposing (Community)
import Pagination
import Routing exposing (Route)


type Msg
    = SetCurrentDate Date
    | UrlChange Route
    | NavigateTo Route
    | CommunityPagination (Pagination.Msg Community)
