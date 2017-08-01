module Messages exposing (..)

import Date exposing (Date)
import Communities exposing (Community)
import Pagination
import Routing exposing (Route, FilterParam)


type Msg
    = SetCurrentDate Date
    | UrlChange Route
    | NavigateTo Route
    | UpdateSearchString String
    | SubmitSearchForm
    | CommunityPagination (Pagination.Msg Community)
