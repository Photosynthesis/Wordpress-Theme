module Update exposing (update)

import Navigation
import Messages exposing (Msg(..))
import Model exposing (Model, paginationConfig)
import Pagination exposing (Pagination)
import Ports
import Routing exposing (Route(..), reverse)


updateUrl : Route -> Model -> ( Model, Cmd Msg )
updateUrl route model =
    let
        withUpdatedRoute =
            { model | route = route }

        jumpToPage model page =
            let
                ( updatedPagination, paginationCmd ) =
                    Pagination.jumpTo paginationConfig model.communities page
            in
                ( { model | communities = updatedPagination }
                , Cmd.map CommunityPagination paginationCmd
                )
    in
        case route of
            Listings page ->
                if page /= Pagination.getPage model.communities then
                    jumpToPage model page
                else
                    ( model, Cmd.none )


update : Msg -> Model -> ( Model, Cmd Msg )
update msg model =
    case msg of
        SetCurrentDate currentDate ->
            ( { model | currentDate = Just currentDate }, Cmd.none )

        UrlChange newRoute ->
            updateUrl newRoute model

        NavigateTo newRoute ->
            ( model
            , Cmd.batch
                [ Navigation.newUrl <| reverse newRoute
                , Ports.scrollTo "main"
                ]
            )

        CommunityPagination subMsg ->
            let
                ( paginationModel, paginationCmd ) =
                    Pagination.update subMsg model.communities
            in
                ( { model | communities = paginationModel }
                , Cmd.map CommunityPagination paginationCmd
                )
