module Directory.Update exposing (update)

{-| Contains Functions For Modifying the Application State.
-}

import Directory.Commands as Commands
import Directory.Messages exposing (Msg(..))
import Directory.Model exposing (Model, paginationConfig)
import Directory.Pagination as Pagination
import Directory.Routing as Routing exposing (Route(..), ListingsRoute(..), FilterParam(..))
import Gallery
import RemoteData


updateUrl : Route -> Model -> ( Model, Cmd Msg )
updateUrl newRoute model =
    case newRoute of
        ListingsRoute listingsRoute ->
            listingsUpdateUrl listingsRoute model

        DetailsRoute slug ->
            detailsUpdateUrl slug model


{-| Make Model Changes & Queue Commands Related to ListingsRoute Page Changes.
-}
listingsUpdateUrl : ListingsRoute -> Model -> ( Model, Cmd Msg )
listingsUpdateUrl route model =
    let
        updatedModel =
            { model | route = ListingsRoute route, searchString = updatedSearchString }

        jumpToPage page =
            let
                ( updatedPagination, paginationCmd ) =
                    Pagination.jumpTo paginationConfig model.communities page
            in
                ( { updatedModel | communities = updatedPagination }
                , Cmd.map CommunityPagination paginationCmd
                )

        communityFilters =
            Pagination.getData model.communities
                |> (\{ filters } -> filters)

        communityOrdering =
            Pagination.getData model.communities
                |> (\{ ordering } -> ordering)

        ( page, filters ) =
            Routing.getPageAndFilters route

        ordering =
            Routing.getOrdering route

        updatedRequestData =
            Pagination.getData model.communities
                |> (\data -> { data | filters = filters, ordering = ordering })

        updatedSearchString =
            Routing.getSearchFilter filters |> Maybe.withDefault ""

        fromDetailsPage =
            case model.route of
                DetailsRoute _ ->
                    True

                ListingsRoute _ ->
                    False
    in
        if filters /= communityFilters || ordering /= communityOrdering || fromDetailsPage then
            Pagination.updateData paginationConfig model.communities updatedRequestData
                |> (\( m, c ) ->
                        ( { updatedModel | communities = m }, Cmd.map CommunityPagination c )
                   )
        else if page /= Pagination.getPage model.communities then
            jumpToPage page
        else
            ( model, Cmd.none )


{-| Make Model Changes & Queue Commands Related to DetailsRoute Page Changes.
-}
detailsUpdateUrl : String -> Model -> ( Model, Cmd Msg )
detailsUpdateUrl slug model =
    ( { model
        | route = DetailsRoute slug
        , community = RemoteData.Loading
        , communityGallery = Gallery.initial
        , communityValidation = RemoteData.NotAsked
      }
    , Commands.getCommunity model.wpNonce slug
    )


{-| Update the Model Based According to Some Message.
-}
update : Msg -> Model -> ( Model, Cmd Msg )
update msg model =
    case msg of
        SetCurrentDate currentDate ->
            ( { model | currentDate = Just currentDate }, Cmd.none )

        UrlChange newRoute ->
            updateUrl newRoute model

        NavigateTo newRoute ->
            ( model, Commands.newPage newRoute )

        UpdateSearchString newString ->
            ( { model | searchString = newString }
            , Cmd.none
            )

        SubmitSearchForm ->
            let
                newRoute =
                    case model.route of
                        ListingsRoute listingsRoute ->
                            Routing.mapFilters replaceSearchFilter listingsRoute
                                |> Routing.mapPage (always 1)
                                |> ListingsRoute

                        DetailsRoute _ ->
                            model.route

                replaceSearchFilter filters =
                    case filters of
                        (SearchFilter _) :: fs ->
                            SearchFilter model.searchString :: fs

                        f :: fs ->
                            f :: replaceSearchFilter fs

                        [] ->
                            [ SearchFilter model.searchString ]
            in
                ( model, Commands.newPage newRoute )

        CommunityPagination subMsg ->
            let
                ( paginationModel, paginationCmd ) =
                    Pagination.update paginationConfig subMsg model.communities

                hasPageChanged =
                    case model.route of
                        ListingsRoute listingsRoute ->
                            Tuple.first (Routing.getPageAndFilters listingsRoute)
                                /= Pagination.getPage paginationModel

                        DetailsRoute _ ->
                            True

                pageChangeCmd =
                    case model.route of
                        ListingsRoute listingsRoute ->
                            if hasPageChanged then
                                listingsRoute
                                    |> Routing.mapPage (always <| Pagination.getPage paginationModel)
                                    |> ListingsRoute
                                    |> Commands.newPage
                            else
                                Cmd.none

                        DetailsRoute _ ->
                            Cmd.none
            in
                ( { model | communities = paginationModel }
                , Cmd.batch
                    [ Cmd.map CommunityPagination paginationCmd
                    , pageChangeCmd
                    ]
                )

        FetchCommunityDetails details ->
            ( { model | community = details }, Cmd.none )

        VerifyCommunityClicked ->
            case model.community of
                RemoteData.Success community ->
                    ( { model | communityValidation = RemoteData.Loading }
                    , Commands.validateCommunity model.wpNonce community.id
                    )

                _ ->
                    ( model, Cmd.none )

        ValidateCommunity isValid ->
            ( { model | communityValidation = isValid }, Cmd.none )

        GalleryMsg subMsg ->
            let
                galleryConfig =
                    Gallery.Config .thumbnailUrl .imageUrl

                updatedModel =
                    case model.community of
                        RemoteData.Success community ->
                            { model
                                | communityGallery =
                                    Gallery.update galleryConfig
                                        subMsg
                                        model.communityGallery
                                    <|
                                        allImages community
                            }

                        _ ->
                            model

                allImages community =
                    case community.image of
                        Nothing ->
                            community.galleryImages

                        Just image ->
                            image :: community.galleryImages
            in
                ( updatedModel, Cmd.none )
